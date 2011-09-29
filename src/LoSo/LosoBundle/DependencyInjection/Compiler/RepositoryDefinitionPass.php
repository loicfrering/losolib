<?php

namespace LoSo\LosoBundle\DependencyInjection\Compiler;

use LoSo\LosoBundle\DependencyInjection\Loader\Annotation\DoctrineServicesUtils;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * CompilerPass which registers Doctrine entity metadatas necessary
 * for repositories into the container.
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class RepositoryDefinitionPass implements CompilerPassInterface
{
    private $doctrineServicesUtils;

    public function process(ContainerBuilder $container)
    {
        $this->doctrineServiceUtils = new DoctrineServicesUtils();

        foreach ($container->findTaggedServiceIds('loso.doctrine.repository') as $repositoryId => $tag) {
            $entity = $tag[0]['entity'];
            $entityManager = $tag[0]['entityManager'];

            $this->processRepositoryDefinition($entity, $entityManager, $container->getDefinition($repositoryId));
            $this->registerEntityMetadata($entity, $entityManager, $container);
        }
    }

    private function processRepositoryDefinition($entity, $entityManager, $definition)
    {
        $entityManagerRef = $this->doctrineServiceUtils->getEntityManagerReference($entityManager);
        $entityMetadataRef = $this->doctrineServiceUtils->getEntityMetadataReference($entity, $entityManager);
        $definition->setArguments(array($entityManagerRef, $entityMetadataRef));
    }

    private function registerEntityMetadata($entity, $entityManager, $container)
    {
        $definition = $this->doctrineServiceUtils->getEntityMetadataDefinition($entity, $entityManager);
        $id = $this->doctrineServiceUtils->resolveEntityMetadataId($entity, $entityManager);
        $container->setDefinition($id, $definition);
    }
}
