<?php

namespace LoSo\Symfony\Components\DependencyInjection\Loader\Annotation;

use Symfony\Components\DependencyInjection\Definition;

/**
 * Description of AbstractAnnotation
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
abstract class AbstractAnnotation
{
    protected $_name;

    public function  __construct($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    abstract public function reflectConstructor(\Zend_Reflection_Method $constructor, Definition $definition);
    abstract public function reflectProperty(\Zend_Reflection_Property $property, Definition $definition);
    abstract public function reflectMethod(\Zend_Reflection_Method $method, Definition $definition);

    protected function _getTag(\Zend_Reflection_Docblock $docblock)
    {
        return $docblock->getTag($this->getName());
    }

    protected function _filterUnderscore($value)
    {
        if(strpos($value, '_') === 0) {
            return substr($value, 1);
        }
        return $value;
    }

    protected function _filterSetPrefix($value)
    {
        if(strpos($value, 'set') === 0) {
            return lcfirst(substr($value, 3));
        }
        return lcfirst($value);
    }
}
