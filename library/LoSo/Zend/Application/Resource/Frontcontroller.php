<?php
class LoSo_Zend_Application_Resource_Frontcontroller extends Zend_Application_Resource_Frontcontroller
{
    /**
     * Retrieve front controller instance
     *
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        if (null === $this->_front) {
            $this->_front = Zend_Controller_Front::getInstance();
            if(!$this->_front->getDispatcher() instanceof  LoSo_Zend_Controller_Dispatcher_SymfonyContainerDispatcher) {
                $dispatcher = new LoSo_Zend_Controller_Dispatcher_SymfonyContainerDispatcher();
                $this->_front->setDispatcher($dispatcher);
            }
        }
        return $this->_front;
    }
}
