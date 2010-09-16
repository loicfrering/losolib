<?php
/**
 * Provide support to Dependency Injection into test cases.
 *
 * @category   Zend
 * @package    LoSo_Zend
 * @subpackage Test
 * @author     Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Test_SymfonyContainerAwareTestCase extends LoSo_Zend_Test_TestCase
{
    /**
     * Check for dependencies and inject them if needed.
     */
    protected function setUp()
    {
        parent::setUp();
        if(Zend_Registry::isRegistered(LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap::getRegistryIndex())
            && ($container = Zend_Registry::get(LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap::getRegistryIndex())) instanceof \Symfony\Component\DependencyInjection\ContainerInterface) {
            $r = new Zend_Reflection_Class($this);
            $properties = $r->getProperties();

            foreach($properties as $property) {
                if($property->getDocComment() && $property->getDocComment()->hasTag('Inject')) {
                    $injectTag = $property->getDocComment()->getTag('Inject');
                    $serviceName = $injectTag->getDescription();
                    if(empty($serviceName)) {
                        $serviceName = $this->_formatServiceName($property->getName());
                    }
                    if($container->has($serviceName)) {
                        $property->setAccessible(true);
                        $property->setValue($this, $container->get($serviceName));
                    }
                }
            }
        }
    }

    /**
     * Format service name.
     *
     * @param  string $serviceName
     * @return string
     */
    protected function _formatServiceName($serviceName)
    {
        if(strpos($serviceName, '_') === 0) {
            $serviceName = substr($serviceName, 1);
        }
        return $serviceName;
    }
}
