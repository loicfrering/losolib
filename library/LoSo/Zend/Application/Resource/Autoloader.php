<?php
/**
 * Description of LoSo_Zend_Application_Resource_Autoloader
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Application_Resource_Autoloader extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $container = $this->getBootstrap()->getContainer();
        $autoloader = new LoSo_Zend_Application_Module_Autoloader($container, array(
            'namespace' => 'Default_',
            'basePath'  => APPLICATION_PATH,
        ));
        return $autoloader;
    }
}
