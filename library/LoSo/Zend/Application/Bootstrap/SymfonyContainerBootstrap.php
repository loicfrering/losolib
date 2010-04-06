<?php
/**
 * Description of LoSo_Zend_Application_Bootstrap_Bootstrap
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected static $_registryIndex = 'container';

    public function getContainer()
    {
        $options = $this->getOption('bootstrap');

        if(null === $this->_container && $options['container']['type'] == 'symfony') {
            $autoloader = Zend_Loader_Autoloader::getInstance();
            $autoloader->pushAutoloader(array('LoSo_Symfony_Components_Autoloader', 'autoload'));
            $sfContainerOptions = isset($options['container']['symfony']) ? $options['container']['symfony'] : array();
            $container = LoSo_Symfony_Components_ServiceContainerFactory::getContainer($sfContainerOptions);
            $this->_container = $container;
            Zend_Registry::set(self::getRegistryIndex(), $container);
            Zend_Controller_Action_HelperBroker::addHelper(new LoSo_Zend_Controller_Action_Helper_DependencyInjection());
        }
        return parent::getContainer();
    }

    public static function getRegistryIndex()
    {
        return self::$_registryIndex;
    }

    public static function setRegistryIndex($registryIndex)
    {
        self::$_registryIndex = $registryIndex;
        return $this;
    }
}
