<?php

namespace LoSo\LosoBundle\DependencyInjection\Loader\Annotation\DefinitionBuilder;

use LoSo\LosoBundle\DependencyInjection\Loader\Annotation\ServiceIdGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class ControllerDefinitionBuilder extends AbstractServiceDefinitionBuilder
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

        $definition->addTag('loso.controller');

        return array('id' => $id, 'definition' => $definition);
    }
}
