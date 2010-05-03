<?php

namespace LoSo\Symfony\Components\DependencyInjection\Loader\Annotation;

use Symfony\Components\DependencyInjection\Definition;
use Symfony\Components\DependencyInjection\Reference;

/**
 * Description of InjectAnnotation
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class InjectAnnotation extends AbstractAnnotation
{
    public function  __construct()
    {
        parent::__construct('Inject');
    }

    public function reflectConstructor(\Zend_Reflection_Method $constructor, Definition $definition)
    {
        $parameters = $constructor->getParameters();
        foreach($parameters as $parameter) {
            $serviceReference = new Reference($this->_extractServiceNameFromParameter($parameter));
            $definition->addArgument($serviceReference);
        }
    }

    public function reflectProperty(\Zend_Reflection_Property $property, Definition $definition)
    {
        $propertyName = $this->_filterUnderscore($property->getName());
        $serviceReference = new Reference($this->_extractServiceNameFromProperty($property));
        $definition->addMethodCall('set' . ucfirst($propertyName), array($serviceReference));
    }

    public function reflectMethod(\Zend_Reflection_Method $method, Definition $definition)
    {
        $serviceReference = new Reference($this->_extractServiceNameFromMethod($method));
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
