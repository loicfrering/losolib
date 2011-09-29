<?php
namespace LoSo\LosoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    private $bundles;

    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    public function getConfigTreeBuilder()
    {
        $bundles = $this->bundles;

        $builder = new TreeBuilder();
        $rootNode = $builder->root('loso');

        $rootNode
            ->children()
                ->arrayNode('service_scan')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('scan')
                    ->beforeNormalization()
                        ->always()
                        ->then(function($v) use ($bundles) {
                            foreach($v as $scan => $value) {
                                $v[$scan]['is_bundle'] = in_array($scan, $bundles);
                            }
                            return $v;
                        })
                    ->end()
                    ->prototype('array')
                        ->validate()
                            ->ifTrue(function($v) { return $v['is_bundle'] && !empty($v['dir']); })
                            ->thenInvalid('"dir" must not be set for a bundle.')
                        ->end()
                        ->validate()
                            ->ifTrue(function($v) { return !$v['is_bundle'] && !empty($v['base_namespace']); })
                            ->thenInvalid('"base_namespace" must only be set for a bundle.')
                        ->end()
                        ->validate()
                            ->ifTrue(function($v) { return !$v['is_bundle'] && empty($v['dir']); })
                            ->thenInvalid('"dir" must be set for arbitrary keys, define bundles otherwise.')
                        ->end()
                        ->children()
                            ->booleanNode('is_bundle')->end()
                            ->arrayNode('base_namespace')
                                ->beforeNormalization()->ifString()->then(function($v) { return array($v); })->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('dir')
                                ->beforeNormalization()->ifString()->then(function($v) { return array($v); })->end()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
