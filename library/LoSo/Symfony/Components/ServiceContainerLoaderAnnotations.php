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
        return $definition;
    }

    protected function _reflectServiceName(Zend_Reflection_Class $r)
    {
        $className = $r->getName();
        if(false !== ($pos = strrpos($className, '_'))) {
            return lcfirst(substr($className, $pos + 1));
        }
        return $serviceName = lcfirst($className);
    }
}
