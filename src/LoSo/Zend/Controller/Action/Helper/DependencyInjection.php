<?php
/**
 * Dependency Injection helper.
 *
 * @category   Zend
 * @package    LoSo_Zend_Controller_Action
 * @subpackage Helper
 * @author     Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Controller_Action_Helper_DependencyInjection extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Symfony Dependency Injection container.
     *
     * @var    \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $_container;

    /**
     * Direct helper implementation:
     *  - return service if exists
     *  - else return parameter if exists
     *
     * @param  string $name
     */
    public function direct($name)
    {
        if($this->_container->has($name)) {
            return $this->_container->getService($name);
        }
        else if($this->_container->getParameterBag()->has($name)) {
            return $this->_container->getParameter($name);
        }
        return null;
    }

    /**
     * Retrieve Symfony Dependency Injection container.
     *
     * @throws LoSo_Exception if container is not a Symfony Depency Injection container
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer() {
        if (null === $this->_container) {
            $this->_container = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getContainer();
            if (!$this->_container instanceof \Symfony\Component\DependencyInjection\ContainerInterface) {
                throw new LoSo_Exception('You must use Symfony Dependency Injection container to use this helper.');
            }
        }
        return $this->_container;
    }
}
