<?php
/**
 * Usage:
 * $this->_helper->logger->debug('Debug');
 * $this->_helper->logger->info('Info');
 * $this->_helper->logger->notice('Notice');
 * $this->_helper->logger->warn('Warning');
 * $this->_helper->logger->err('Error');
 * $this->_helper->logger('Critical', Zend_Log::CRIT);
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Controller_Action_Helper_Logger extends Zend_Controller_Action_Helper_Abstract
{
    static protected $_logger = null;

    public function __construct($loggerOrUrl = null)
    {
        if($loggerOrUrl instanceof Zend_Log) {
            $logger = $loggerOrUrl;
        }
        else if(is_string($loggerOrUrl) && !empty($loggerOrUrl)) {
            $writer = new Zend_Log_Writer_Stream($loggerOrUrl);
            $logger = new Zend_Log($writer);
        }
        else {
            $writer = new Zend_Log_Writer_Stream('php://stderr');
            $logger = new Zend_Log($writer);
        }
        self::$_logger = $logger;
    }

    public function  __call($method, $params)
    {
        self::$_logger->__call($method, $params);
    }

    function log($message, $priority)
    {
        self::$_logger->log($message, $priority);
    }

    function direct($message, $priority)
    {
        $this->log($message, $priority);
    }
}
