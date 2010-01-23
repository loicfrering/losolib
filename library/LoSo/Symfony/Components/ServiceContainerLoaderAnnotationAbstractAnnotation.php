<?php
/**
 * Description of LoSo_Symfony_Components_ServiceContainerLoaderAnnotationAbstractAnnotation
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
abstract class LoSo_Symfony_Components_ServiceContainerLoaderAnnotationAbstractAnnotation
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

    abstract public function reflectConstructor(Zend_Reflection_Method $constructor, sfServiceDefinition $definition);
    abstract public function reflectProperty(Zend_Reflection_Property $property, sfServiceDefinition $definition);
    abstract public function reflectMethod(Zend_Reflection_Method $method, sfServiceDefinition $definition);

    protected function _getTag(Zend_Reflection_Docblock $docblock)
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
