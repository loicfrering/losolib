<?php

namespace LoSo\Symfony\Components\DependencyInjection\Loader;

use Symfony\Components\DependencyInjection\BuilderConfiguration;
use Symfony\Components\DependencyInjection\Definition;
use Symfony\Components\DependencyInjection\Reference;

/**
 * Description of LoSo_Symfony_Components_ServiceContainerLoaderAnnotations
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class ZendControllerLoader extends AnnotationLoader
{
    public function  __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/Annotation/Controller.php';
    }

    protected function reflectDefinition(BuilderConfiguration $configuration, $reflClass)
    {
        $definition = new Definition($reflClass->getName());

        if ($annot = $this->reader->getClassAnnotation($reflClass, 'LoSo\Symfony\Components\DependencyInjection\Loader\Annotation\Controller')) {
            $id = 'zend.controller.' . $reflClass->getName();

            $this->reflectProperties($reflClass, $definition);
            $this->reflectMethods($reflClass, $definition);
            $this->reflectConstructor($reflClass, $definition);

            $configuration->setDefinition($id, $definition);
        }
    }

    protected function reflectConstructor($reflClass, $definition)
    {
        $definition->addArgument(new Reference('zend.controller.request'));
        $definition->addArgument(new Reference('zend.controller.response'));
        $definition->addArgument(new Reference('zend.controller.params'));
    }
}

