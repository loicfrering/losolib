<?php

namespace LoSo\Symfony\Components\DependencyInjection\Loader\Annotation;

/**
 * Description of Value annotation
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class Value extends AbstractAnnotation
{
    public function defineFromConstructor($constructor, $definition)
    {
        throw new RuntimeException("@{$this->getName()} annotation does not provide constructor support");
    }

    public function defineFromProperty($property, $definition)
    {
        $propertyName = $this->filterUnderscore($property->getName());
        $parameterName = $this->extractParameterNameFromProperty($property);
        $definition->addMethodCall('set' . ucfirst($propertyName), array($parameterName));
    }

    public function defineFromMethod($method, $definition)
    {
        $parameterName = $this->extractParameterNameFromMethod($method);
        $definition->addMethodCall($method->getName(), array($parameterName));
    }


    protected function extractParameterNameFromProperty($property)
    {
        return $this->value ?: '%' . $this->filterUnderscore($property->getName()) . '%';
    }

    protected function extractParameterNameFromMethod($method)
    {
        return $this->value ?: '%' . $this->filterSetPrefix($method->getName()) . '%';
    }
}
