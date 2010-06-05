<?php
abstract class LoSo_Zend_Tool_Provider_Scaffold_Doctrine_Abstract extends LoSo_Zend_Tool_Provider_Scaffold_Abstract
{
    protected $metadata;

    protected function _getMetadata()
    {
        if(null === $this->metadata) {
            $this->bootstrap->bootstrap('Doctrine2');
            $em = $this->bootstrap->getResource('Doctrine2');
            $cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory($em);
            $metadatas = $cmf->getAllMetadata();
            $entityName = $this->_getEntityName();
            $entityClass = $this->_getModuleNamespace() . '_Model_' . $entityName;
            $metadata = current(\Doctrine\ORM\Tools\Console\MetadataFilter::filter($metadatas, $entityClass));
            $this->metadata = $metadata;
        }
        return $this->metadata;
    }
}
