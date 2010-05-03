<?php
/**
 * Description of LoSo_Symfony_Components_ServiceContainerLoaderAnnotations
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Symfony_Components_ServiceContainerLoaderZendController extends LoSo_Symfony_Components_ServiceContainerLoaderAnnotations
{
    protected $_definitions = array();
    protected $_annotations = array();

    protected function _reflect($file)
    {
        require_once $file;
        $r = new Zend_Reflection_File($file);
        try {
            $r = $r->getClass();
            if($r->getDocblock()->hasTag('Service')) {
                $serviceName = $this->_reflectServiceName($r);
                $definition = $this->_reflectDefinition($r);
                $this->_definitions[$serviceName] = $definition;
            }
        }
        catch(Zend_Reflection_Exception $e) {
        }
        catch(ReflectionException $e) {
        }
    }

    protected function _reflectConstructor(Zend_Reflection_Class $r, sfServiceDefinition $definition)
    {
        $definition->addArgument(new sfServiceReference('zend.controller.request'));
        $definition->addArgument(new sfServiceReference('zend.controller.response'));
        $definition->addArgument(new sfServiceReference('zend.controller.params'));
    }

    protected function _reflectServiceName(Zend_Reflection_Class $r)
    {
        $className = $r->getName();
        return 'zend.controller.' . $className;
    }
}

