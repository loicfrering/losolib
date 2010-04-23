<?php
abstract class LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Abstract extends LoSo_Zend_Tool_Provider_Scaffold_Abstract
{
    protected $metadata;

    protected function _getMetadata()
    {
        if(null === $this->metadata) {
            $entityName = $this->_getEntityName();
            Zend_Loader_Autoloader::getInstance()->registerNamespace('Doctrine');
            $entityClass = $this->_getModuleNamespace() . '_Model_' . $entityName;
            $metadata = new Doctrine\ORM\Mapping\ClassMetadata($entityClass);
            $reader = new \Doctrine\Common\Annotations\AnnotationReader(new \Doctrine\Common\Cache\ArrayCache);
            $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
            $driver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader);
            $driver->loadMetadataForClass($entityClass, $metadata);
            $this->metadata = $metadata;
        }
        return $this->metadata;
    }
}
