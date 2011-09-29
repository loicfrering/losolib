<?php

namespace LoSo\LosoBundle\DependencyInjection\Loader\Annotation\DefinitionBuilder;

use LoSo\LosoBundle\DependencyInjection\Loader\Annotation\ServiceIdGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class ServiceDefinitionBuilder extends AbstractServiceDefinitionBuilder
{
    public function build(\ReflectionClass $reflClass, $annot)
    {
        $definitionHolder = parent::build($reflClass, $annot);
        $definition = $definitionHolder['definition'];
        $id = $annot->value ?: $annot->name;
        if (empty($id)) {
            $serviceIdGenerator = new ServiceIdGenerator();
            $id = $serviceIdGenerator->generate($reflClass);
        }

        if (isset($annot->scope)) {
            $definition->setScope($annot->scope);
        }

        if (isset($annot->public)) {
            $definition->setPublic($annot->public);
        }

        if (isset($annot->factoryMethod)) {
            $definition->setFactoryMethod($annot->factoryMethod);
        }

        if (isset($annot->factoryService)) {
            $definition->setFactoryService($annot->factoryService);
        }

        if (isset($annot->configurator)) {
            if (is_string($annot->configurator)) {
                $definition->setConfigurator($annot->configurator);
            } else {
                $definition->setConfigurator(array($this->resolveServices($annot->configurator[0]), $annot->configurator[1]));
            }
        }

        if (isset($annot->tags)) {
            if (!is_array($annot->tags)) {
                throw new \InvalidArgumentException(sprintf('Parameter "tags" must be an array for service "%s" in %s.', $id, $reflClass->getName()));
            }
            foreach ($annot->tags as $tag) {
                if (!isset($tag['name'])) {
                    throw new \InvalidArgumentException(sprintf('A "tags" entry is missing a "name" key must be an array for service "%s" in %s.', $id, $reflClass->getName()));
                }
                $name = $tag['name'];
                unset($tag['name']);

                $definition->addTag($name, $tag);
            }
        }

        return array('id' => $id, 'definition' => $definition);
    }

    /**
     * Resolves services.
     *
     * @param string $value
     * @return void
     */
    private function resolveServices($value)
    {
        if (is_array($value)) {
            $value = array_map(array($this, 'resolveServices'), $value);
        } else if (is_string($value) && 0 === strpos($value, '@')) {
            if (0 === strpos($value, '@?')) {
                $value = substr($value, 2);
                $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            } else {
                $value = substr($value, 1);
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
}
