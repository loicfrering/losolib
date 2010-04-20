<?php
class LoSo_Zend_Tool_Provider_Manifest implements Zend_Tool_Framework_Manifest_ProviderManifestable
{
    public function getProviders()
    {
        require_once 'LoSo/Zend/Tool/Provider/Scaffold.php';
        return array(
            new LoSo_Zend_Tool_Provider_Scaffold() 
        );
    }
}
