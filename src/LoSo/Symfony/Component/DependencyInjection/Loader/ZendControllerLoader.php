<?php

namespace LoSo\Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Description of LoSo_Symfony_Component_ServiceContainerLoaderAnnotations
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class ZendControllerLoader extends AnnotationLoader
{
    public function  __construct(ContainerBuilder $container)
    {
        parent::__construct($container);
        require_once __DIR__ . '/Annotation/Controller.php';
    }

    protected function reflectDefinition($reflClass)
    {
        $definition = new Definition($reflClass->getName());

        if ($annot = $this->reader->getClassAnnotation($reflClass, 'LoSo\Symfony\Component\DependencyInjection\Loader\Annotation\Controller')) {
            $id = 'zend.controller.' . $reflClass->getName();

            $this->reflectProperties($reflClass, $definition);
            $this->reflectMethods($reflClass, $definition);
            $this->reflectConstructor($reflClass, $definition);

            $this->container->setDefinition($id, $definition);
        }
    }

    protected function reflectConstructor($reflClass, $definition)
    {
        $definition->addArgument(new Reference('zend.controller.request'));
        $definition->addArgument(new Reference('zend.controller.response'));
        $definition->addArgument(new Reference('zend.controller.params'));
    }
}

