<?php
/**
 * The manifest for scaffolding CLI tool build upon Zend_Tool_Framework.
 *
 * @category   Zend
 * @package    LoSo_Zend_Tool
 * @subpackage Provider
 * @author     Loïc Frering <loic.frering@gmail.com>
 */
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
