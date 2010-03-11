<?php
/**
 * Description of Atos_Zend_Application_Resource_Navigation
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Application_Resource_Navigation extends Zend_Application_Resource_Navigation
{
    public function init()
    {
        $options = $this->getOptions();

        if(isset($options['configFile'])) {
            $this->setOptions($this->_loadConfig($options['configFile']));
        }

        parent::init();
    }

    protected function _loadConfig($file)
    {
        $environment = $this->getBootstrap()->getEnvironment();
        $suffix      = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'ini':
                $config = new Zend_Config_Ini($file, $environment);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($file, $environment);
                break;

            case 'php':
            case 'inc':
                $config = include $file;
                if (!is_array($config)) {
                    throw new Zend_Application_Exception('Invalid configuration file provided; PHP file does not return array value');
                }
                return $config;
                break;

            default:
                throw new Zend_Application_Exception('Invalid configuration file provided; unknown config type');
        }

        return $config->toArray();
    }
}
