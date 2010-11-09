<?php
class Scaffold_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initResourceLoader()
    {
        $resourceLoader = $this->getResourceLoader();
        $resourceLoader->addResourceType('dao', 'dao', 'Dao');
    }
}
