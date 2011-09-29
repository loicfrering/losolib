<?php

namespace LoSo\LosoBundle\DependencyInjection\Loader\Annotation\DefinitionBuilder;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Loïc Frering <loic.frering@gmail.com>
 */
abstract class AbstractServiceDefinitionBuilder extends AbstractAnnotationDefinitionBuilder
{
    private static $injectAnnot = 'LoSo\LosoBundle\DependencyInjection\Annotations\Inject';

    public function build(\ReflectionClass $reflClass, $annot)
    {
        $definition = new Definition($reflClass->getName());

        if (null !== ($constructor = $reflClass->getConstructor())) {
            $this->processConstructor($constructor, $definition);
        }
        $this->processProperties($reflClass->getProperties(), $definition);
        $this->processMethods($reflClass->getMethods(), $definition, $reflClass);

        return array('id' => null, 'definition' => $definition);
    }

    private function processConstructor($constructor, $definition)
    {
        if ($annot = $this->reader->getMethodAnnotation($constructor, self::$injectAnnot)) {
            $arguments = $this->extractReferencesForMethod($constructor, $annot);
            $definition->setArguments($arguments);
        }
    }

    private function processProperties($properties, $definition)
    {
        foreach ($properties as $property) {
            if ($annot = $this->reader->getPropertyAnnotation($property, self::$injectAnnot)) {
                $propertyName = $this->filterUnderscore($property->getName());
                $reference = $this->extractReferenceForProperty($property, $annot);
                $definition->addMethodCall('set' . ucfirst($propertyName), array($reference));
            }
        }
    }

    private function processMethods($methods, $definition, $reflClass)
    {
        foreach ($methods as $method) {
            if (strpos($method->getName(), 'set') === 0) {
                if ($annot = $this->reader->getMethodAnnotation($method, self::$injectAnnot)) {
                    $arguments = $this->extractReferencesForMethod($method, $annot);
                    $definition->addMethodCall($method->getName(), $arguments);
                }
            }
        }
    }

    private function extractReferencesForMethod($method, $annot)
    {
        $arguments = array();
        $parameters = $method->getParameters();
        if (null === $annot->value) {
            foreach ($parameters as $parameter) {
                $arguments[] = new Reference($parameter->getName());
            }
        }
        else {
            if (!is_array($annot->value)) {
                $annot->value = array($annot->value);
            }
            if (count($annot->value) != $method->getNumberOfParameters()) {
                throw new \InvalidArgumentException(sprintf('Annotation "@Inject" when specifying services id must have one id per method argument for "%s::%s"', $method->getDeclaringClass()->getName(), $method->getName()));
            }
            $arguments = $this->resolveServices($annot->value);
        }
        return $arguments;
    }

    private function extractReferenceForProperty($property, $annot)
    {
        if ($annot->value) {
            if (!is_string($annot->value)) {
                throw new \InvalidArgumentException(sprintf('Annotation "@Inject" when specifying services id on property must have one string value for "%s::%s"', $property->getDeclaringClass()->getName(), $property->getName()));
            }
            return $this->resolveServices($annot->value);
        }
        return new Reference($this->filterUnderscore($property->getName()));
    }

    private function resolveServices($value)
    {
        if (is_array($value)) {
            $value = array_map(array($this, 'resolveServices'), $value);
        } else if (is_string($value) &&  false === strpos($value, '%')) {
            if (0 === strpos($value, '?')) {
                $value = substr($value, 1);
                $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            } else {
                $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
            }

            if ('=' === substr($value, -1)) {
                $value = substr($value, 0, -1);
                $strict = false;
            } else {
                $strict = true;
            }

            $value = new Reference($value, $invalidBehavior, $strict);
        }

        return $value;
    }

    private function filterUnderscore($value)
    {
        if(strpos($value, '_') === 0) {
            return substr($value, 1);
        }
        return $value;
    }
}
