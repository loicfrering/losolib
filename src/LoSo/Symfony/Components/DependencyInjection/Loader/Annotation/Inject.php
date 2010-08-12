<?php

namespace LoSo\Symfony\Components\DependencyInjection\Loader\Annotation;

use Symfony\Components\DependencyInjection\Reference;

final class Inject extends AbstractAnnotation
{
    public function defineFromConstructor($constructor, $definition)
    {
        $parameters = $constructor->getParameters();
        foreach($parameters as $parameter) {
            $serviceReference = new Reference($this->extractReferenceNameFromParameter($parameter));
            $definition->addArgument($serviceReference);
        }
    }

    public function defineFromProperty($property, $definition)
    {
        $propertyName = $this->filterUnderscore($property->getName());
        $serviceReference = new Reference($this->extractReferenceNameFromProperty($property));
        $definition->addMethodCall('set' . ucfirst($propertyName), array($serviceReference));
    }

    public function defineFromMethod($method, $definition)
    {
        $serviceReference = new Reference($this->extractReferenceNameFromMethod($method));
        $definition->addMethodCall($method->getName(), array($serviceReference));
    }


    protected function extractReferenceNameFromProperty($property)
    {
        return $this->value ?: $this->filterUnderscore($property->getName());
    }

    protected function extractReferenceNameFromMethod($method)
    {
        return $this->value ?: $this->filterSetPrefix($method->getName());
    }

    protected function extractReferenceNameFromParameter($parameter)
    {
        return $parameter->getName();
    }
}
