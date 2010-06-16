<?php
/**
 * For dependency injection concerns, this class extends Zend_Controller_Action for
 * moving init() method call into the dispatcher instead of the constructur
 *
 * @category   Zend
 * @package    LoSo_Zend
 * @subpackage Controller
 * @author     Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Controller_Action extends Zend_Controller_Action
{
    /**
     * {@inheritdoc}
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        $this->setRequest($request)
             ->setResponse($response)
             ->_setInvokeArgs($invokeArgs);
        $this->_helper = new Zend_Controller_Action_HelperBroker($this);
    }
}
