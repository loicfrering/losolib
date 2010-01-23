<?php
/**
 * Description of LoSo_Symfony_Components_ServiceContainerLoaderAnnotations
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Symfony_Components_ServiceContainerLoaderAnnotations extends sfServiceContainerLoader
{
    protected $_definitions = array();
    
    public function doLoad($path)
    {
        try {
            $directoryIterator = new DirectoryIterator($path);
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
                if($constructor->getDocblock()->hasTag('Inject')) {
                    $parameters = $constructor->getParameters();
                    Zend_Debug::dump($parameters, 'Constructor parameters:');
                    foreach($parameters as $parameter) {
                        $serviceReference = new sfServiceReference($this->_getInjectedServiceNameFromParameter($parameter));
                        $definition->addArgument($serviceReference);
                    }
                }
            }
        }
        catch(ReflectionException $e) {

        }
        catch(Zend_Reflection_Exception $e) {

        }
    }

    protected function _reflectProperties(Zend_Reflection_Class $r, sfServiceDefinition $definition)
    {
        $properties = $r->getProperties();
        foreach($properties as $property) {
            if($property->getDeclaringClass()->getName() == $r->getName()) {
                $docblock = $property->getDocComment();
                if($docblock && $docblock->hasTag('Inject')) {
                    $propertyName = $this->_formatPropertyName($property->getName());
                    $serviceReference = new sfServiceReference($this->_getInjectedServiceNameFromProperty($property));
                    $definition->addMethodCall('set' . ucfirst($propertyName), array($serviceReference));
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
                    if($method->getDocblock()->hasTag('Inject')) {
                        $serviceReference = new sfServiceReference($this->_getInjectedServiceNameFromMethod($method));
                        $definition->addMethodCall($method->getName(), array($serviceReference));
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
        $serviceTagDescription = $r->getDocblock()->getTag('Service')->getDescription();
        if(!empty($serviceTagDescription)) {
            return $serviceTagDescription;
        }
        else if(false !== ($pos = strrpos($className, '_'))) {
            return lcfirst(substr($className, $pos + 1));
        }
        return lcfirst($className);
    }

    protected function _getInjectedServiceNameFromParameter(Zend_Reflection_Parameter $p)
    {
        return $p->getName();
    }

    protected function _getInjectedServiceNameFromProperty(Zend_Reflection_Property $p)
    {
        $propertyName = $p->getName();
        $injectTagDescription = $p->getDocComment()->getTag('Inject')->getDescription();
        if(!empty($injectTagDescription)) {
            return $injectTagDescription;
        }
        return $this->_formatPropertyName($propertyName);
    }

    protected function _getInjectedServiceNameFromMethod(Zend_Reflection_Method $m)
    {
        $methodName = $m->getName();
        $injectTagDescription = $m->getDocblock()->getTag('Inject')->getDescription();
        if(!empty($injectTagDescription)) {
            return $injectTagDescription;
        }
        return $this->_formatMethodName($methodName);
    }

    protected function _formatPropertyName($propertyName)
    {
        if(strpos($propertyName, '_') === 0) {
            return substr($propertyName, 1);
        }
        return $propertyName;
    }

    protected function _formatMethodName($methodName)
    {
        if(strpos($methodName, 'set') === 0) {
            return lcfirst(substr($methodName, 3));
        }
        return lcfirst($methodName);
    }
}
