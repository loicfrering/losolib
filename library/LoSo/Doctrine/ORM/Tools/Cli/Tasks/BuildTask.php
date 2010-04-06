<?php

namespace LoSo\Doctrine\ORM\Tools\Cli\Tasks;

use Doctrine\Common\Cli\Tasks\AbstractTask,
    Doctrine\Common\Cli\CliController,
    Doctrine\Common\Cli\CliException,
    Doctrine\Common\Cli\Configuration,
    Doctrine\Common\Cli\Option,
    Doctrine\Common\Cli\OptionGroup;

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
class BuildTask extends AbstractTask
{
    protected $_cli;

    protected function _getDoctrineCli()
    {
        if ($this->_cli === null) {
            $configuration = new Configuration($em);
            $this->_cli = new CliController($configuration);
        }
        $em = $this->getConfiguration()->getAttribute('em');
        $this->_cli->getConfiguration()->setAttribute('em', $em);
        return $this->_cli;
    }

    protected function _runDoctrineCliTask($name, $options = array())
    {
        $builtOptions = array();
        foreach ($options as $key => $value) {
            if ($value === null) {
                $builtOptions[] = sprintf('--%s', $key);
            }
            else {
                $builtOptions[] = sprintf('--%s=%s', $key, $value);
            }
        }
        return $this->_getDoctrineCli()->run(array_merge(array('doctrine', $name), $builtOptions));
    }

    protected function _getContainer()
    {
        return \Zend_Registry::get(LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap::getRegistryIndex());
    }

    /**
     * @inheritdoc
     */
    public function buildDocumentation()
    {
        $schemaOption = new OptionGroup(OptionGroup::CARDINALITY_1_1, array(
            new Option(
                'all', null,
                'Build everything and reset the database.'
            ),
            new Option(
                'entities', null,
                'Build model classes.'
            ),
            new Option(
                'db', null,
                'Drop database, create database and create schema.'
            ),
        ));
        
        /*$optionalOptions = new OptionGroup(OptionGroup::CARDINALITY_0_N, array(
            new Option('dump-sql', null, 'Instead of try to apply generated SQLs into EntityManager, output them.'),
            new Option('class-dir', '<PATH>', 'Optional class directory to fetch for Entities.')
        ));*/
        
        $doc = $this->getDocumentation();
        $doc->setName('build')
            ->setDescription('Build task for easily re-building your Doctrine development environment.')
            ->getOptionGroup()
                ->addOption($schemaOption);
                //->addOption($optionalOptions);
    }
    
    /**
     * @inheritdoc
     */
    public function validate()
    {
        $arguments = $this->getArguments();
        $em = $this->getConfiguration()->getAttribute('em');
        
        if ($em === null) {
            throw new CliException(
                "Attribute 'em' of CLI Configuration is not defined or it is not a valid EntityManager."
            );
        }

        $isBuildAll = isset($arguments['all']) && $arguments['all'];
        $isBuildEntities = isset($arguments['entities']) && $arguments['entities'];
        $isBuildDb = isset($arguments['db']) && $arguments['db'];
        
        if ($isBuildAll && ($isBuildEntities || $isBuildDb)) {
            throw new CliException(
                'You cannot use --all with --entities or --db.'
            );
        }

        if ( ! ($isBuildAll || $isBuildEntities || $isBuildDb) ) {
            throw new CliException(
                'You must specify at a minimum one of the options: ' .
                '--all, --entities or --db.'
            );
        }
        
        return true;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if(\Zend_Registry::isRegistered(\LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap::getRegistryIndex())
            && ($container = \Zend_Registry::get(\LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap::getRegistryIndex())) instanceof \sfServiceContainer) {
            $mappingDir = $container->getParameter('doctrine.orm.mapping_dir');
            $entitiesDir = $container->getParameter('doctrine.orm.entities_dir');
        }
        else {
            $doctrineConfig = \Zend_Registry::get('doctrine.config');
            $mappingDir = $doctrineConfig['doctrine.orm.mapping_dir'];
            $entitiesDir = $doctrineConfig['doctrine.orm.entities_dir'];
        }

        $arguments = $this->getArguments();
        $printer = $this->getPrinter();

        $isBuildAll = isset($arguments['all']) && $arguments['all'];
        $isBuildEntities = isset($arguments['entities']) && $arguments['entities'];
        $isBuildDb = isset($arguments['db']) && $arguments['db'];
        

        if ($isBuildAll || $isBuildEntities) {
            $options = array(
                'from' => APPLICATION_PATH . '/' . $mappingDir,
                'to' => 'annotation',
                'dest' => APPLICATION_PATH . '/' . $entitiesDir
            );
            $this->_runDoctrineCliTask('orm:convert-mapping', $options);
            $this->_processResourceNamespaces(APPLICATION_PATH . '/' . $entitiesDir);
        }
        
        if ($isBuildAll || $isBuildDb) {
            if($isBuildAll) {
                $this->getPrinter()->writeln('');
            }
            $options = array(
                're-create' => true,
                'class-dir' => APPLICATION_PATH . '/' . $entitiesDir
            );
            $this->_runDoctrineCliTask('orm:schema-tool', $options);
        }
    }

        protected function _processResourceNamespaces($path)
    {
        $directoryIterator = new \DirectoryIterator($path);
        foreach($directoryIterator as $fileInfo) {
            if($fileInfo->isFile()) {
                $suffix = strtolower(pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION));
                if($suffix == 'php') {
                    $path = $fileInfo->getPath();
                    $fileName = $fileInfo->getBasename();
                    if(strpos($fileName, '_') !== false) {
                        $newFileName = substr($fileName, strrpos($fileName, '_') + 1);
                        rename($path . DIRECTORY_SEPARATOR . $fileName, $path . DIRECTORY_SEPARATOR . $newFileName);
                    }
                }
            }
        }
    }
}
