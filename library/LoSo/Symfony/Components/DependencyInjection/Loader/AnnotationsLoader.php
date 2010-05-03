<?php

namespace LoSo\Symfony\Components\DependencyInjection\Loader;

use Symfony\Components\DependencyInjection\Definition;
use Symfony\Components\DependencyInjection\Loader\Loader;
use Symfony\Components\DependencyInjection\BuilderConfiguration;
use LoSo\Symfony\Components\DependencyInjection\Loader\Annotation\InjectAnnotation;
use LoSo\Symfony\Components\DependencyInjection\Loader\Annotation\ValueAnnotation;

/**
 * Description of LoSo_Symfony_Components_ServiceContainerLoaderAnnotations
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class AnnotationsLoader extends Loader
{
    protected $_definitions = array();
    protected $_annotations = array();

    public function  __construct()
    {
        $this->_annotations = array(
            new InjectAnnotation(),
            new ValueAnnotation()
        );
    }
    
    public function load($path)
    {
        $configuration = new BuilderConfiguration();

        try {
            $directoryIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            foreach($directoryIterator as $fileInfo) {
                if($fileInfo->isFile()) {
                    $suffix = strtolower(pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION));
                    if($suffix == 'php') {
                        $this->_reflect($configuration, $fileInfo->getPathname());
                    }
                }
            }
        }
        catch(UnexpectedValueException $e) {
            
        }

        return $configuration;
    }

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

    protected function _reflectDefinition(\Zend_Reflection_Class $r)
    {
        $definition = new Definition($r->getName());

        $this->_reflectConstructor($r, $definition);
        $this->_reflectProperties($r, $definition);
        $this->_reflectMethods($r, $definition);

        return $definition;
    }

    protected function _reflectConstructor(\Zend_Reflection_Class $r, Definition $definition)
    {
        try {
            $constructor = $r->getMethod('__construct');
            if(null !== $constructor) {
                foreach($this->_annotations as $annotation) {
                    if($constructor->getDocblock()->hasTag($annotation->getName())) {
                        $annotation->reflectConstructor($constructor, $definition);
                    }
                }
            }
        }
        catch(\Zend_Reflection_Exception $e) {
        }
        catch(\ReflectionException $e) {
        }
    }

    protected function _reflectProperties(\Zend_Reflection_Class $r, Definition $definition)
    {
        $properties = $r->getProperties();
        foreach($properties as $property) {
            if($property->getDocComment() && $property->getDeclaringClass()->getName() == $r->getName()) {
                $docblock = $property->getDocComment();
                foreach($this->_annotations as $annotation) {
                    if($docblock->hasTag($annotation->getName())) {
                        $annotation->reflectProperty($property, $definition);
                    }
                }
            }
        }
    }

    protected function _reflectMethods(\Zend_Reflection_Class $r, Definition $definition)
    {
        $methods = $r->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach($methods as $method) {
            if($method->getDeclaringClass()->getName() == $r->getName() && strpos($method->getName(), 'set') === 0) {
                try {
                    foreach($this->_annotations as $annotation) {
                        if($method->getDocblock()->hasTag($annotation->getName())) {
                            $annotation->reflectMethod($method, $definition);
                        }
                    }
                }
                catch(\Zend_Reflection_Exception $e) {
                }
            }
        }
    }

    protected function _reflectServiceName(\Zend_Reflection_Class $r)
    {
        $className = $r->getName();
        $serviceTagDescription = trim($r->getDocblock()->getTag('Service')->getDescription());
        if(!empty($serviceTagDescription)) {
            return $serviceTagDescription;
        }
        else if(false !== ($pos = strrpos($className, '_'))) {
            return lcfirst(substr($className, $pos + 1));
        }
        return lcfirst($className);
    }
}
