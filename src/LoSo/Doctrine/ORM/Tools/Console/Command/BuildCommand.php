<?php

namespace LoSo\Doctrine\ORM\Tools\Console\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console,
    Doctrine\ORM\Tools\Console\MetadataFilter,
    Doctrine\ORM\Tools\Export\ClassMetadataExporter,
    Doctrine\ORM\Tools\EntityGenerator,
    Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;

/**
 * Build task for easily re-building your Doctrine development environment.
 * 
 * This task has the following arguments:
 * 
 * <tt>--entities</tt>
 * Build model classes.
 * 
 * <tt>--db</tt>
 * Drop database, create database and create schema.
 * 
 * <tt>--all</tt>
 * Build everything and reset the database.
 * 
 * @author  Loïc Frering <loic.frering@gmail.com>
 */
class BuildCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('loso:build')
        ->setDescription('Build task for easily re-building your Doctrine development environment.')
        ->setDefinition(array(
            new InputOption(
                'generate-annotations', null, InputOption::VALUE_OPTIONAL,
                'Flag to define if generator should generate annotation metadata on entities.', false
            ),
            new InputOption(
                'generate-methods', null, InputOption::VALUE_OPTIONAL,
                'Flag to define if generator should generate stub methods on entities.', true
            ),
            new InputOption(
                'regenerate-entities', null, InputOption::VALUE_OPTIONAL,
                'Flag to define if generator should regenerate entity if it exists.', false
            ),
            new InputOption(
                'update-entities', null, InputOption::VALUE_OPTIONAL,
                'Flag to define if generator should only update entity if it exists.', true
            ),
            new InputOption(
                'extend', null, InputOption::VALUE_OPTIONAL,
                'Defines a base class to be extended by generated entity classes.'
            ),
            new InputOption(
                'num-spaces', null, InputOption::VALUE_OPTIONAL,
                'Defines the number of indentation spaces', 4
            )
        ))
        ->setHelp(<<<EOT
Build task for easily re-building your Doctrine development environment.
EOT
        );
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $em = $this->getHelper('em')->getEntityManager();

        if(\Zend_Registry::isRegistered(\LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap::getRegistryIndex())
            && ($container = \Zend_Registry::get(\LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap::getRegistryIndex())) instanceof \Symfony\Component\DependencyInjection\ContainerInterface) {
            $mappingPaths = $container->getParameter('doctrine.orm.mapping_paths');
            $entitiesPaths = $container->getParameter('doctrine.orm.entities_paths');
        }
        else {
            $doctrineConfig = \Zend_Registry::get('doctrine.config');
            $mappingPaths = $doctrineConfig['doctrine.orm.mapping_paths'];
            $entitiesPaths = $doctrineConfig['doctrine.orm.entities_paths'];
        }

        $cmf = new DisconnectedClassMetadataFactory($em);
        $metadatas = $cmf->getAllMetadata();

        foreach($mappingPaths as $namespace => $mappingPath) {
            // Process destination directory
            $destPath = realpath($entitiesPaths[$namespace]);

            if ( ! file_exists($destPath)) {
                throw new \InvalidArgumentException(
                    sprintf("Entities destination directory '<info>%s</info>' does not exist.", $destPath)
                );
            } else if ( ! is_writable($destPath)) {
                throw new \InvalidArgumentException(
                    sprintf("Entities destination directory '<info>%s</info>' does not have write permissions.", $destPath)
                );
            }

            $moduleMetadatas = MetadataFilter::filter($metadatas, $namespace);
            if (count($moduleMetadatas)) {
                // Create EntityGenerator
                $entityGenerator = new EntityGenerator();

                $entityGenerator->setGenerateAnnotations($input->getOption('generate-annotations'));
                $entityGenerator->setGenerateStubMethods($input->getOption('generate-methods'));
                $entityGenerator->setRegenerateEntityIfExists($input->getOption('regenerate-entities'));
                $entityGenerator->setUpdateEntityIfExists($input->getOption('update-entities'));
                $entityGenerator->setNumSpaces($input->getOption('num-spaces'));

                if (($extend = $input->getOption('extend')) !== null) {
                    $entityGenerator->setClassToExtend($extend);
                }

                foreach ($moduleMetadatas as $metadata) {
                    $output->write(sprintf('Processing entity "<info>%s</info>"', $metadata->name) . PHP_EOL);
                }

                // Generating Entities
                $entityGenerator->generate($moduleMetadatas, $destPath);
                $this->_processNamespaces($destPath, $namespace);

                // Outputting information message
                $output->write(sprintf('Entity classes generated to "<info>%s</INFO>"', $destPath) . PHP_EOL);


            } else {
                $output->write('No Metadata Classes to process.' . PHP_EOL);
            }

        }

        /*$output->write(PHP_EOL . 'Reset database.' . PHP_EOL);

        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $output->write('Dropping database schema...' . PHP_EOL);
        $schemaTool->dropSchema($metadatas);
        $output->write('Database schema dropped successfully!' . PHP_EOL);
        $output->write('Creating database schema...' . PHP_EOL);
        $schemaTool->createSchema($metadatas);
        $output->write('Database schema created successfully!' . PHP_EOL);*/
    }

    protected function _processNamespaces($path, $baseNamespace)
    {
        $directoryIterator = new \DirectoryIterator($path);
        foreach($directoryIterator as $fileInfo) {
            if($fileInfo->isFile()) {
                $suffix = strtolower(pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION));
                if($suffix == 'php') {
                    $path = $fileInfo->getPath();
                    $fileName = $fileInfo->getBasename();
                    if(strpos($fileName, '_') !== false) {
                        $newFileName = str_replace($baseNamespace, '', $fileName);
                        rename($path . DIRECTORY_SEPARATOR . $fileName, $path . DIRECTORY_SEPARATOR . $newFileName);
                    }
                }
            }
        }
    }
}
