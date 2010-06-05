<?php
abstract class LoSo_Zend_Tool_Provider_Scaffold_Abstract
{
    protected $entityName;
    protected $module;
    protected $moduleNamespace;
    protected $bootstrap;

    public function __construct($entityName, $module, $moduleNamespace, $bootstrap = null)
    {
        $this->entityName = $entityName;
        $this->module = $module;
        $this->moduleNamespace = $moduleNamespace;
        $this->bootstrap = $bootstrap;
    }

    abstract public function scaffold();

    protected function _getEntityName()
    {
        return $this->entityName;
    }

    protected function _getEntityVarName()
    {
        return lcfirst($this->entityName);
    }

    protected function _getEntitiesVarName()
    {
        return $this->_getEntityVarName() . 's';
    }

    protected function _getModule()
    {
        return $this->module;
    }

    protected function _getModuleNamespace()
    {
        return $this->moduleNamespace;
    }
}
