<?php
/**
 * Description of DependencyInjection
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Controller_Action_Helper_DependencyInjection extends Zend_Controller_Action_Helper_Abstract
{
    /**
     *
     * @var sfServiceContainer
     */
    protected $_container;

    public function direct($name)
    {
        if($this->_container->hasService($name)) {
            return $this->_container->getService($name);
        }
        else if($this->_container->hasParameter($name)) {
            return $this->_container->getParameter($name);
        }
        return null;
    }

    public function  getContainer() {
        return $this->_container;
    }
}
