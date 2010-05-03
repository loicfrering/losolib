<?php

namespace LoSo\Symfony\Components\DependencyInjection\Loader\Annotation;

use Symfony\Components\DependencyInjection\Definition;

/**
 * Description of ValueAnnotation
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class ValueAnnotation extends AbstractAnnotation
{
    public function  __construct()
    {
        parent::__construct('Value');
    }
    
    public function reflectConstructor(\Zend_Reflection_Method $constructor, Definition $definition)
    {
        throw new RuntimeException("@{$this->getName()} annotation does not provide constructor support");
    }

    public function reflectProperty(\Zend_Reflection_Property $property, Definition $definition)
    {
        $propertyName = $this->_filterUnderscore($property->getName());
        $parameterName = $this->_extractParameterNameFromProperty($property);
        $definition->addMethodCall('set' . ucfirst($propertyName), array($parameterName));
    }

    public function reflectMethod(\Zend_Reflection_Method $method, Definition $definition)
    {
        $parameterName = $this->_extractParameterNameFromMethod($method);
        $definition->addMethodCall($method->getName(), array($parameterName));
    }


    protected function _extractParameterNameFromProperty($p)
    {
        $propertyName = $p->getName();
        $tagDescription = $this->_getTag($p->getDocComment())->getDescription();
        if(!empty($tagDescription)) {
            return $tagDescription;
        }
        return '%' . $this->_filterUnderscore($propertyName) . '%';
    }

    protected function _extractParameterNameFromMethod($m)
    {
        $methodName = $m->getName();
        $tagDescription = $this->_getTag($m->getDocblock())->getDescription();
        if(!empty($tagDescription)) {
            return $tagDescription;
        }
        return '%' . $this->_filterSetPrefix($methodName) . '%';
    }
}
