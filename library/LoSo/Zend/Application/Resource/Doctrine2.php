<?php
use Doctrine\Common\ClassLoader,
    Doctrine\ORM\Configuration,
    Doctrine\ORM\EntityManager,
    Doctrine\Common\Cache\ApcCache,
    Doctrine\Common\Cache\MemcacheCache,
    Doctrine\Common\Cache\XcacheCache,
    Doctrine\Common\Cache\ArrayCache;

/**
 * An application resource for initializing your Doctrine2 environment
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Application_Resource_Doctrine2 extends Zend_Application_Resource_ResourceAbstract
{
    protected $_config;

    public function init()
    {
        $options = $this->getOptions();

        $this->_config = new Configuration;

        // Parameters
        $this->_initParameters();

        // Set up caches
        $this->_initCache();

        // Proxy configuration
        $this->_initProxy();

        // Database connection information
        $connectionOptions = $this->_initConnection();

        // Create EntityManager
        $em = EntityManager::create($connectionOptions, $this->_config);
        $this->getBootstrap()->getContainer()->em = $em;
        return $em;
    }

    protected function _initCache()
    {
        $options = $this->getOptions();
        switch($options['cache']) {
            case 'apc':
                $cache = new ApcCache();
                break;

            case 'memcache':
                $cache = new MemcacheCache();
                break;

            case 'xcache':
                $cache = new XcacheCache();
                break;

            default:
                $cache = new ArrayCache();
        }
        $this->_config->setMetadataCacheImpl($cache);
        $this->_config->setQueryCacheImpl($cache);
    }

    protected function _initProxy()
    {
        $options = $this->getOptions();
        $this->_config->setProxyDir(isset($options['proxy']['directory']) ? $options['proxy']['directory'] : APPLICATION_PATH . '/doctrine2/Proxies');
        $this->_config->setProxyNamespace(isset($options['proxy']['namespace']) ? $options['proxy']['namespace'] : 'Proxies');
    }

    protected function _initConnection()
    {
        $options = $this->getOptions();
        return $options['connection'];
    }

    protected function _initParameters()
    {
        $options = $this->getOptions();
        $container = $this->getBootstrap()->getApplication()->getBootstrap()->getContainer();
        if($container instanceof sfServiceContainer) {
            $container->setParameter('doctrine.orm.mapping_dir', $options['config']['mappingDir']);
            $container->setParameter('doctrine.orm.entities_dir', $options['config']['entitiesDir']);
        }
        else {
            Zend_Registry::set('doctrine.config', array(
                'doctrine.orm.mapping_dir' => $options['config']['mappingDir'],
                'doctrine.orm.entities_dir' => $options['config']['entitiesDir']
            ));
        }
    }
}
