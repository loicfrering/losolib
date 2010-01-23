<?php
/**
 * Description of LoSo_Symfony_Components_ServiceContainerLoaderAnnotationInject
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Symfony_Components_ServiceContainerLoaderAnnotationInject extends LoSo_Symfony_Components_ServiceContainerLoaderAnnotationAbstractAnnotation
{
    public function  __construct()
    {
        parent::__construct('Inject');
    }

    public function reflectConstructor(Zend_Reflection_Method $constructor, sfServiceDefinition $definition)
    {
        $parameters = $constructor->getParameters();
        foreach($parameters as $parameter) {
            $serviceReference = new sfServiceReference($this->_extractServiceNameFromParameter($parameter));
            $definition->addArgument($serviceReference);
        }
    }

    public function reflectProperty(Zend_Reflection_Property $property, sfServiceDefinition $definition)
    {
        $propertyName = $this->_filterUnderscore($property->getName());
        $serviceReference = new sfServiceReference($this->_extractServiceNameFromProperty($property));
        $definition->addMethodCall('set' . ucfirst($propertyName), array($serviceReference));
    }

    public function reflectMethod(Zend_Reflection_Method $method, sfServiceDefinition $definition)
    {
        $serviceReference = new sfServiceReference($this->_extractServiceNameFromMethod($method));
        $definition->addMethodCall($method->getName(), array($serviceReference));
    }


    protected function _extractServiceNameFromParameter($p)
    {
        return $p->getName();
    }

    protected function _extractServiceNameFromProperty($p)
    {
        $propertyName = $p->getName();
        $tagDescription = $this->_getTag($p->getDocComment())->getDescription();
        if(!empty($tagDescription)) {
            return $tagDescription;
        }
        return $this->_filterUnderscore($propertyName);
    }

    protected function _extractServiceNameFromMethod($m)
    {
        $methodName = $m->getName();
        $tagDescription = $this->_getTag($m->getDocblock())->getDescription();
        if(!empty($tagDescription)) {
            return $tagDescription;
        }
        return $this->_filterSetPrefix($methodName);
    }
}
