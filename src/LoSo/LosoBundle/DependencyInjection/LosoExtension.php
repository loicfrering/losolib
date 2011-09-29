<?php
namespace LoSo\LosoBundle\DependencyInjection;

use LoSo\LosoBundle\DependencyInjection\Configuration;
use LoSo\LosoBundle\DependencyInjection\Loader\AnnotationLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * LosoExtension for LosoBundle.
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LosoExtension extends Extension
{
    private $loader;

    public function load(array $configs, ContainerBuilder $container)
    {
        $this->setUpLoader($container);
        $processor = new Processor();
        $bundles = $container->getParameter('kernel.bundles');
        $configuration = new Configuration(array_keys($bundles));
        $config = $processor->processConfiguration($configuration, $configs);

        if (isset($config['service_scan'])) {
            foreach ($config['service_scan'] as $scanName => $scanConfig) {
                if ($scanConfig['is_bundle']) {
                    $this->loadBundle($scanName, $bundles[$scanName], $scanConfig, $container);
                } else {
                    $this->loadDirectories($scanConfig['dir']);
                }
            }
        }
    }

    private function setUpLoader(ContainerBuilder $container)
    {
        $this->loader = new AnnotationLoader($container);
    }

    private function loadDirectories(array $directories)
    {
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->loadDir($dir);
            } else {
                throw new \InvalidArgumentException(sprintf('Invalid scan directory "%s".', $dir));
            }
        }
    }

    private function loadBundle($bundleName, $bundleClass, array $config)
    {
        $bundle = new \ReflectionClass($bundleClass);
        $bundleDir = dirname($bundle->getFilename());
        if (!empty($config['base_namespace'])) {
            foreach ($config['base_namespace'] as $baseNamespace) {
                $dir = $bundleDir . '/' . $baseNamespace;
                if (is_dir($dir)) {
                    $this->loadDir($dir);
                } else {
                    throw new \InvalidArgumentException(sprintf('Invalid base namespace "%s" for bundle "%s".', $baseNamespace, $bundleName));
                }
            }
        } else {
            $this->loadDir($bundleDir);
        }
    }

    private function loadDir($dir)
    {
        return $this->loader->load($dir);
    }
}
