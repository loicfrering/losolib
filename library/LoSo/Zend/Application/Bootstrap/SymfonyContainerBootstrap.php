<?php
/**
 * Description of LoSo_Zend_Application_Bootstrap_Bootstrap
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected static $_registryIndex = 'container';

    protected $_doCache;
    protected $_cacheExists;
    protected $_cacheFile;

    public function run()
    {
        // Load service container if not cached or if we want to cache and cache doesn't esist
        if(!$this->_doCache() || ($this->_doCache() && !$this->_cacheExists())) {
            $this->_loadControllersInContainer();
        }
        // Cache loaded service container if we want to cache and cache doesn't already exist
        if($this->_doCache() && !$this->_cacheExists()) {
            $this->_cacheContainer();
        }
        parent::run();
    }

    public function getContainer()
    {
        $options = $this->getOption('bootstrap');

        if(null === $this->_container && $options['container']['type'] == 'symfony') {
            if ($this->_doCache() && $this->_cacheExists()) {
                $cacheFile = $this->_getCacheFile();
                $cacheName = pathinfo($cacheFile, PATHINFO_FILENAME);
                require_once $cacheFile;
                $this->_container = new $cacheName();
            }
            else {
                $this->_container = new \Symfony\Components\DependencyInjection\Builder();
                $this->_loadContainer();
                $this->_cacheContainer();
            }

            Zend_Registry::set(self::getRegistryIndex(), $this->_container);
            Zend_Controller_Action_HelperBroker::addHelper(new LoSo_Zend_Controller_Action_Helper_DependencyInjection());
        }
        return parent::getContainer();
    }

    protected function _doCache()
    {
        if(null === $this->_doCache) {
            $options = $this->getOption('bootstrap');
            $sfContainerOptions = isset($options['container']['symfony']) ? $options['container']['symfony'] : array();
            $this->_doCache = isset($sfContainerOptions['cache']) ? (bool) $sfContainerOptions['cache'] : false;
        }
        return $this->_doCache;
    }

    protected function _cacheExists()
    {
        if(null === $this->_cacheExists) {
            $cacheFile = $this->_getCacheFile();
            $this->_cacheExists = file_exists($cacheFile);
        }
        return $this->_cacheExists;
    }

    protected function _getCacheFile()
    {
        if(null === $this->_cacheFile) {
            $options = $this->getOption('bootstrap');
            $sfContainerOptions = isset($options['container']['symfony']) ? $options['container']['symfony'] : array();
            if(isset($sfContainerOptions['cacheFile'])) {
                $cacheFile = $sfContainerOptions['cacheFile'];
            }
            else {
                $cacheFile = sys_get_temp_dir() . '/ServiceContainer.php';
            }

            $this->_cacheFile = $cacheFile;
        }
        return $this->_cacheFile;
    }

    protected function _loadContainer()
    {
        $options = $this->getOption('bootstrap');
        $sfContainerOptions = isset($options['container']['symfony']) ? $options['container']['symfony'] : array();

        $container = $this->getContainer();
        $configuration = new \Symfony\Components\DependencyInjection\BuilderConfiguration();

        // Load configuration files
        if(isset($sfContainerOptions['configFiles'])) {
            foreach($sfContainerOptions['configFiles'] as $file) {
                $configuration->merge($this->_loadConfigFile($file));
            }
        }
        // Load configuration paths for annotated classes
        if(isset($sfContainerOptions['configPaths'])) {
            foreach($sfContainerOptions['configPaths'] as $path) {
                $configuration->merge($this->_loadPath($path));
            }
        }

        $container->merge($configuration);
    }

    protected function _loadControllersInContainer()
    {
        $container = $this->getContainer();
        $configuration = new \Symfony\Components\DependencyInjection\BuilderConfiguration();

        // Load controllers into service container
        $loader = new \LoSo\Symfony\Components\DependencyInjection\Loader\ZendControllerLoader($this->getContainer());
        $front = $this->getResource('FrontController');
        $controllerDirectories = $front->getControllerDirectory();
        foreach ($controllerDirectories as $controllerDirectory) {
            $configuration->merge($loader->load($controllerDirectory));
        }

        $container->merge($configuration);
    }

    protected function _cacheContainer()
    {
        $cacheFile = $this->_getCacheFile();
        $cacheName = pathinfo($cacheFile, PATHINFO_FILENAME);
        $dumper = new \Symfony\Components\DependencyInjection\Dumper\PhpDumper($this->getContainer());
        file_put_contents($cacheFile, $dumper->dump(array('class' => $cacheName)));
    }

    protected function _loadConfigFile($file)
    {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'xml':
                $loader = new \Symfony\Components\DependencyInjection\Loader\XmlFileLoader();
                break;

            case 'yml':
                $loader = new \Symfony\Components\DependencyInjection\Loader\YamlFileLoader();
                break;

            case 'ini':
                $loader = new \Symfony\Components\DependencyInjection\Loader\IniFileLoader();
                break;

            default:
                throw new \LoSo\Symfony\Components\Exception("Invalid configuration file provided; unknown config type '$suffix'");
        }
        return $loader->load($file);
    }

    protected function _loadPath($path)
    {
        $loader = new \LoSo\Symfony\Components\DependencyInjection\Loader\AnnotationsLoader();
        return $loader->load($path);
    }

    public static function getRegistryIndex()
    {
        return self::$_registryIndex;
    }

    public static function setRegistryIndex($registryIndex)
    {
        self::$_registryIndex = $registryIndex;
        return $this;
    }
}
