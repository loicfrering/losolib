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

    public function preDispatch()
    {
        $actionController = $this->getActionController();
        $this->_container = $actionController->getInvokeArg('bootstrap')->getContainer();

        $r = new Zend_Reflection_Class($actionController);
        $properties = $r->getProperties();

        foreach($properties as $property) {
            if($property->getDeclaringClass()->getName() == get_class($actionController)) {
                if($property->getDocComment()->hasTag('Inject')) {
                    $injectTag = $property->getDocComment()->getTag('Inject');
                    $serviceName = $injectTag->getDescription();
                    if(empty($serviceName)) {
                        $serviceName = $this->_formatServiceName($property->getName());
                    }
                    if($this->_container->hasService($serviceName)) {
                        $property->setAccessible(true);
                        $property->setValue($actionController, $this->_container->getService($serviceName));
                    }
                }
            }
        }
        
    }

    protected function _formatServiceName($serviceName)
    {
        if(strpos($serviceName, '_') === 0) {
            $serviceName = substr($serviceName, 1);
        }
        return $serviceName;
    }

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
