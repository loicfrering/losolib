<?php
/**
 * Description of LoSo_Symfony_Components_ServiceContainerLoaderAnnotations
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Symfony_Components_ServiceContainerLoaderAnnotations extends sfServiceContainerLoader
{
    protected $_definitions = array();
    protected $_annotations = array();

    public function  __construct(sfServiceContainerBuilder $container = null)
    {
        $this->_annotations = array(
            new LoSo_Symfony_Components_ServiceContainerLoaderAnnotationInject(),
            new LoSo_Symfony_Components_ServiceContainerLoaderAnnotationValue()
        );
        parent::__construct($container);
    }
    
    public function doLoad($path)
    {
        try {
            $directoryIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
            foreach($directoryIterator as $fileInfo) {
                if($fileInfo->isFile()) {
                    $suffix = strtolower(pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION));
                    if($suffix == 'php') {
                        $this->_reflect($fileInfo->getPathname());
                    }
                }
            }
        }
        catch(UnexpectedValueException $e) {
            
        }

        return array($this->_definitions, array());
    }

    protected function _reflect($file)
    {
        require_once $file;
        $r = new Zend_Reflection_File($file);
        try {
            $r = $r->getClass();
            if($r->getDocblock()->hasTag('Service')) {
                $serviceName = $this->_reflectServiceName($r);
                $definition = $this->_reflectDefinition($r);
                $this->_definitions[$serviceName] = $definition;
            }
        }
        catch(Zend_Reflection_Exception $e) {
        }
        catch(ReflectionException $e) {
        }
    }

    protected function _reflectDefinition(Zend_Reflection_Class $r)
    {
        $definition = new sfServiceDefinition($r->getName());

        $this->_reflectConstructor($r, $definition);
        $this->_reflectProperties($r, $definition);
        $this->_reflectMethods($r, $definition);

        return $definition;
    }

    protected function _reflectConstructor(Zend_Reflection_Class $r, sfServiceDefinition $definition)
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
        catch(Zend_Reflection_Exception $e) {
        }
        catch(ReflectionException $e) {
        }
    }

    protected function _reflectProperties(Zend_Reflection_Class $r, sfServiceDefinition $definition)
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

    protected function _reflectMethods(Zend_Reflection_Class $r, sfServiceDefinition $definition)
    {
        $methods = $r->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach($methods as $method) {
            if($method->getDeclaringClass()->getName() == $r->getName() && strpos($method->getName(), 'set') === 0) {
                try {
                    foreach($this->_annotations as $annotation) {
                        if($method->getDocblock()->hasTag($annotation->getName())) {
                            $annotation->reflectMethod($method, $definition);
                        }
                    }
                }
                catch(Zend_Reflection_Exception $e) {
                }
            }
        }
    }

    protected function _reflectServiceName(Zend_Reflection_Class $r)
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
