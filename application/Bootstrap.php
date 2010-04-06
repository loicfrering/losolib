<?php

class Bootstrap extends LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap
{
    protected function _initResourceLoader()
    {
        $resourceLoader = $this->getResourceLoader();
        $resourceLoader->addResourceType('dao', 'dao', 'Dao');
    }
}

