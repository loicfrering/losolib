<?php

namespace LoSo\LosoBundle\DependencyInjection\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Common\Annotations\AnnotationReader;
use LoSo\LosoBundle\DependencyInjection\Loader\Annotation\DefinitionBuilder\AnnotationDefinitionBuilderInterface;
use LoSo\LosoBundle\DependencyInjection\Loader\Annotation\DefinitionBuilder\ControllerDefinitionBuilder;
use LoSo\LosoBundle\DependencyInjection\Loader\Annotation\DefinitionBuilder\ServiceDefinitionBuilder;
use LoSo\LosoBundle\DependencyInjection\Loader\Annotation\DefinitionBuilder\RepositoryDefinitionBuilder;

/**
 * AnnotationLoader loads annotated class service definitions.
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class AnnotationLoader extends Loader
{
    private $container;
    private $reader;
    private $builders = array();

    public function  __construct(ContainerBuilder $container, array $builders = array())
    {
        $this->container = $container;
        $this->reader = new AnnotationReader();

        if (empty($builders)) {
            $this->setBuilders(array(
                'LoSo\LosoBundle\DependencyInjection\Annotations\Service' => new ServiceDefinitionBuilder($this->reader),
                'LoSo\LosoBundle\DependencyInjection\Annotations\Repository' => new RepositoryDefinitionBuilder($this->reader),
                'LoSo\LosoBundle\DependencyInjection\Annotations\Controller' => new ControllerDefinitionBuilder($this->reader)
            ));
        } else {
            $this->setBuilders($builders);
        }
    }

    public function addBuilder($annotation, $builder)
    {
        if ($builder instanceof AnnotationDefinitionBuilderInterface) {
            $this->builders[$annotation] = $builder;
        } else {
            throw new \InvalidArgumentException('Builder must be an instance of AnnotationDefinitionBuilderInterface.');
        }

        return $this;
    }

    public function addBuilders(array $builders)
    {
        foreach ($builders as $annotation => $builder) {
            $this->addBuilder($annotation, $builder);
        }
        return $this;
    }

    public function setBuilders(array $builders)
    {
        $this->clearBuilders();
        foreach ($builders as $annotation => $builder) {
            $this->addBuilder($annotation, $builder);
        }
        return $this;
    }

    public function clearBuilders()
    {
        $this->builders = array();
        return $this;
    }

    public function load($path, $type = null)
    {
        try {
            $includedFiles = array();
            $directoryIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            foreach($directoryIterator as $fileInfo) {
                if($fileInfo->isFile()) {
                    $suffix = strtolower(pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION));
                    if($suffix == 'php') {
                        $sourceFile = realpath($fileInfo->getPathName());
                        require_once $sourceFile;
                        $includedFiles[] = $sourceFile;
                    }
                }
            }

            $declaredClasses = get_declared_classes();
            foreach($declaredClasses as $className) {
                $reflClass = new \ReflectionClass($className);
                if(in_array($reflClass->getFileName(), $includedFiles)) {
                    $this->reflectDefinition($reflClass);
                }
            }
        }
        catch(UnexpectedValueException $e) {

        }
    }

    public function supports($resource, $type = null)
    {
        return is_dir($resource);
    }

    private function reflectDefinition($reflClass)
    {
        $id = null;
        $definition = null;

        foreach ($this->builders as $annotClass => $builder) {
            if ($annot = $this->reader->getClassAnnotation($reflClass, $annotClass)) {
                $definitionHolder = $builder->build($reflClass, $annot);
                $id = $definitionHolder['id'];
                $definition = $definitionHolder['definition'];
                $this->container->setDefinition($id, $definition);
            }
        }
    }
}
