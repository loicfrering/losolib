<?php
/**
 * Extended navigation resource with support of an external configuration file.
 *
 * @category   Zend
 * @package    LoSo_Zend_Application
 * @subpackage Resource
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Application_Resource_Navigation extends Zend_Application_Resource_Navigation
{
    /**
     * Initialize navigation.
     *
     * @return Zend_Navigation
     */
    public function init()
    {
        $options = $this->getOptions();
        if (isset($options['configFile'])) {
            if (!$this->_container) {
                $config = $this->_loadConfig($options['configFile']);
                $this->_container = new Zend_Navigation($config);
            }

            $this->store();
            return $this->_container;
        } else {
            return parent::init();
        }
    }

    /**
     * Load a navigation configuration file.
     *
     * @param string Path to the configuration file
     */
    protected function _loadConfig($file)
    {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'ini':
                $config = new Zend_Config_Ini($file);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($file);
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

        return $config;
    }
}

