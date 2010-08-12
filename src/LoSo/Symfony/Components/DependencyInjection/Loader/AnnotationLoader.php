<?php

namespace LoSo\Symfony\Components\DependencyInjection\Loader;

use Symfony\Components\DependencyInjection\Loader\Loader;
use Symfony\Components\DependencyInjection\BuilderConfiguration;
use Symfony\Components\DependencyInjection\Definition;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * AnnotationLoader loads annotated class service definitions.
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class AnnotationLoader extends Loader
{
    protected $reader;
    protected $annotations = array();

    public function  __construct()
    {
        $this->annotations = array(
            'LoSo\Symfony\Components\DependencyInjection\Loader\Annotation\Inject',
            'LoSo\Symfony\Components\DependencyInjection\Loader\Annotation\Value'
        );
        $this->reader = new AnnotationReader();
        $this->reader->setDefaultAnnotationNamespace('LoSo\Symfony\Components\DependencyInjection\Loader\Annotation\\');
        //$this->reader->setAutoloadAnnotations(true);
        require_once __DIR__ . '/Annotation/Service.php';
        require_once __DIR__ . '/Annotation/Inject.php';
        require_once __DIR__ . '/Annotation/Value.php';
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
                        $reflClass = $this->getReflectionClassFromFile($fileInfo->getPathname());
                        $this->reflectDefinition($configuration, $reflClass);
                    }
                }
            }
        }
        catch(UnexpectedValueException $e) {
            
        }

        return $configuration;
    }

    protected function getReflectionClassFromFile($file)
    {
        require_once $file;
        $reflFile = new \Zend_Reflection_File($file);
        return $reflFile->getClass();
    }

    protected function reflectDefinition(BuilderConfiguration $configuration, $reflClass)
    {
        $definition = new Definition($reflClass->getName());

        if ($annot = $this->reader->getClassAnnotation($reflClass, 'LoSo\Symfony\Components\DependencyInjection\Loader\Annotation\Service')) {
            $id = $this->extractServiceName($reflClass, $annot);
            $definition->setShared($annot->shared);

            $this->reflectProperties($reflClass, $definition);
            $this->reflectMethods($reflClass, $definition);
            $this->reflectConstructor($reflClass, $definition);

            $configuration->setDefinition($id, $definition);
        }
    }

    protected function reflectProperties($reflClass, $definition)
    {
        foreach ($reflClass->getProperties(-1, 'LoSo_Zend_Reflection_Property') as $property) {
            foreach ($this->annotations as $annotClass) {
                if ($annot = $this->reader->getPropertyAnnotation($property, $annotClass)) {
                    $annot->defineFromProperty($property, $definition);
                }
            }
        }
    }

    protected function reflectMethods($reflClass, $definition)
    {
        foreach ($reflClass->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() == $reflClass->getName() && strpos($method->getName(), 'set') === 0) {
                foreach ($this->annotations as $annotClass) {
                    if ($annot = $this->reader->getMethodAnnotation($method, $annotClass)) {
                        $annot->defineFromMethod($method, $definition);
                    }
                }
            }
        }
    }

    protected function reflectConstructor($reflClass, $definition)
    {
        try {
            $constructor = $reflClass->getMethod('__construct');
            foreach ($this->annotations as $annotClass) {
                if ($annot = $this->reader->getMethodAnnotation($constructor, $annotClass)) {
                    $annot->defineFromConstructor($constructor, $definition);
                }
            }
        } catch (\ReflectionException $e) {

        }
    }

    protected function extractServiceName($reflClass, $annot)
    {
        $serviceName = $annot->value ?: $annot->name;
        
        if (null === $serviceName) {
            $className = $reflClass->getName();
            if(false !== ($pos = strrpos($className, '_'))) {
                $serviceName = lcfirst(substr($className, $pos + 1));
            } else {
                $serviceName = lcfirst($className);
            }
        }

        return $serviceName;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////

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
            if($property->getDocComment()) {
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
