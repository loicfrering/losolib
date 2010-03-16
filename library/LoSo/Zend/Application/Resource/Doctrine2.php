<?php
use Doctrine\Common\ClassLoader,
    Doctrine\ORM\Configuration,
    Doctrine\ORM\EntityManager,
    Doctrine\Common\Cache\ArrayCache;

/**
 * Description of Doctrine2
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Application_Resource_Doctrine2 extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        Zend_Loader_Autoloader::getInstance()->registerNamespace('Doctrine');

        // Set up caches
        $config = new Configuration;
        $cache = new ArrayCache;
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);

        // Proxy configuration
        $config->setProxyDir(__DIR__ . '/Proxies');
        $config->setProxyNamespace('Proxies');

        // Database connection information
        $connectionOptions = array(
            'driver' => 'pdo_mysql',
            'host' => '127.0.0.1',
            'dbname' => 'todo',
            'user' => 'todo',
            'password' => 'todo'
        );

        // Create EntityManager
        $em = EntityManager::create($connectionOptions, $config);
        return $em;
    }
}
