<?php
/**
 * Logger action helper.
 *
 * Usage:
 *   $this->_helper->logger->debug('Debug');
 *   $this->_helper->logger->info('Info');
 *   $this->_helper->logger->notice('Notice');
 *   $this->_helper->logger->warn('Warning');
 *   $this->_helper->logger->err('Error');
 *   $this->_helper->logger('Critical', Zend_Log::CRIT);
 *
 * @category   Zend
 * @package    LoSo_Zend_Controller_Action
 * @subpackage Helper
 * @author     Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Controller_Action_Helper_Logger extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Zend_Log instance.
     *
     * @var Zend_Log
     */
    static protected $_logger = null;

    /**
     * Class constructor.
     *
     * @param Zend_Log|string
     */
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

    /**
     * Magic method __call implemntation proxies to Zend_Log __call method.
     *
     * @param  string $method
     * @param  array  $params
     */
    public function  __call($method, $params)
    {
        self::$_logger->__call($method, $params);
    }

    /**
     * Proxy to Zend_Log log() method.
     *
     * @param  string  $message
     * @param  integer $priority
     */
    function log($message, $priority)
    {
        self::$_logger->log($message, $priority);
    }

    /**
     * Action controller direct implementation proxies to log() method.
     *
     * @param  string  $message
     * @param  integer $priority
     */
    function direct($message, $priority)
    {
        $this->log($message, $priority);
    }
}
