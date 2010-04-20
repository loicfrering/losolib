<?php
abstract class LoSo_Zend_Tool_Provider_Scaffold_Abstract
{
    protected $entityName;
    protected $module;
    protected $appnamespace;

    public function __construct($entityName, $module, $appnamespace)
    {
        $this->entityName = $entityName;
        $this->module = $module;
        $this->appnamespace = $appnamespace;
    }

    abstract public function scaffold();

    protected function _getEntityName()
    {
        return $this->entityName;
    }

    protected function _getModule()
    {
        return $this->module;
    }

    protected function _getAppNamespace()
    {
        return $this->appnamespace;
    }
}
