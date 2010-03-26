<?php
/**
 * Description of LoSo_Zend_Test_SymfonyContainerAwareTestCase
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Test_SymfonyContainerAwareTestCase extends LoSo_Zend_Test_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        if(Zend_Registry::isRegistered('container') && ($container = Zend_Registry::get('container')) instanceof sfServiceContainer) {
            $r = new Zend_Reflection_Class($this);
            $properties = $r->getProperties();

            foreach($properties as $property) {
                if($property->getDocComment() && $property->getDocComment()->hasTag('Inject')) {
                    $injectTag = $property->getDocComment()->getTag('Inject');
                    $serviceName = $injectTag->getDescription();
                    if(empty($serviceName)) {
                        $serviceName = $this->_formatServiceName($property->getName());
                    }
                    if($container->hasService($serviceName)) {
                        $property->setAccessible(true);
                        $property->setValue($this, $container->getService($serviceName));
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
}
