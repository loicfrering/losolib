<?php

namespace LoSo\LosoBundle\DependencyInjection\Loader\Annotation;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class DoctrineServicesUtils
{
    public function getEntityManagerReference($entityManager)
    {
        return new Reference($this->resolveEntityManagerId($entityManager));
    }

    public function resolveEntityManagerId($entityManager)
    {
        $entityManagerRef = 'doctrine.orm.entity_manager';
        if ($entityManager != 'default') {
            $entityManagerRef = sprintf('doctrine.orm.%s_entity_manager', $entityManager);
        }
        return $entityManagerRef;
    }

    public function getEntityMetadataDefinition($entity, $entityManager)
    {
        $definition = new Definition('Doctrine\ORM\Mapping\ClassMetadata');
        $definition->setFactoryService($this->resolveEntityManagerId($entityManager));
        $definition->setFactoryMethod('getClassMetadata');
        $definition->setArguments(array($entity));
        return $definition;
    }

    public function getEntityMetadataReference($entity, $entityManager)
    {
        return new Reference($this->resolveEntityMetadataId($entity, $entityManager));
    }

    public function resolveEntityMetadataId($entity, $entityManager)
    {
        return 'loso.doctrine.metadata.' . $entityManager . '.' . str_replace(array('\\', ':'), '.', $entity);
    }

}
