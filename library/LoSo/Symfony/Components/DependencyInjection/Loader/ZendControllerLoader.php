<?php

namespace LoSo\Symfony\Components\DependencyInjection\Loader;

use Symfony\Components\DependencyInjection\Definition;
use Symfony\Components\DependencyInjection\Reference;
use Symfony\Components\DependencyInjection\BuilderConfiguration;

/**
 * Description of LoSo_Symfony_Components_ServiceContainerLoaderAnnotations
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class ZendControllerLoader extends AnnotationsLoader
{
    protected $_definitions = array();
    protected $_annotations = array();

    protected function _reflect(BuilderConfiguration $configuration, $file)
    {
        require_once $file;
        $r = new \Zend_Reflection_File($file);
        try {
            $r = $r->getClass();
            if($r->getDocblock()->hasTag('Service')) {
                $serviceName = $this->_reflectServiceName($r);
                $definition = $this->_reflectDefinition($r);
                $configuration->setDefinition($serviceName, $definition);
            }
        }
        catch(\Zend_Reflection_Exception $e) {
        }
        catch(\ReflectionException $e) {
        }
    }

    protected function _reflectConstructor(\Zend_Reflection_Class $r, Definition $definition)
    {
        $definition->addArgument(new Reference('zend.controller.request'));
        $definition->addArgument(new Reference('zend.controller.response'));
        $definition->addArgument(new Reference('zend.controller.params'));
    }

    protected function _reflectServiceName(\Zend_Reflection_Class $r)
    {
        $className = $r->getName();
        return 'zend.controller.' . $className;
    }
}

