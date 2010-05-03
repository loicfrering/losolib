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
        // Load service container if no cached or if we want to cache and cache doesn't esist
        if(!$this->_doCache() || ($this->_doCache() && !$this->_cacheExists())) {
            $this->_loadContainer();
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
            $autoloader = Zend_Loader_Autoloader::getInstance();
            $autoloader->pushAutoloader(array('LoSo_Symfony_Components_Autoloader', 'autoload'));

            if ($this->_doCache() && $this->_cacheExists()) {
                $cacheFile = $this->_getCacheFile();
                $cacheName = pathinfo($cacheFile, PATHINFO_FILENAME);
                require_once $cacheFile;
                $container = new $cacheName();
            }
            else {
                $container = new sfServiceContainerBuilder();
            }

            $this->_container = $container;
            Zend_Registry::set(self::getRegistryIndex(), $container);
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

        // Load configuration files
        if(isset($sfContainerOptions['configFiles'])) {
            foreach($sfContainerOptions['configFiles'] as $file) {
                $this->_loadConfigFile($file);
            }
        }
        // Load configuration paths for annotated classes
        if(isset($sfContainerOptions['configPaths'])) {
            foreach($sfContainerOptions['configPaths'] as $path) {
                $this->_loadPath($path);
            }
        }

        // Load controllers into service container
        $loader = new LoSo_Symfony_Components_ServiceContainerLoaderZendController($this->getContainer());
        $front = $this->getResource('FrontController');
        $controllerDirectories = $front->getControllerDirectory();
        foreach ($controllerDirectories as $controllerDirectory) {
            $loader->load($controllerDirectory);
        }
    }

    protected function _cacheContainer()
    {
        $cacheFile = $this->_getCacheFile();
        $cacheName = pathinfo($cacheFile, PATHINFO_FILENAME);
        $dumper = new sfServiceContainerDumperPhp($this->getContainer());
        file_put_contents($cacheFile, $dumper->dump(array('class' => $cacheName)));
    }

    protected function _loadConfigFile($file)
    {
        $container = $this->getContainer();
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'xml':
                $loader = new sfServiceContainerLoaderFileXml($container);
                break;

            case 'yml':
                $loader = new sfServiceContainerLoaderFileYaml($container);
                break;

            case 'ini':
                $loader = new sfServiceContainerLoaderFileIni($container);
                break;

            default:
                throw new LoSo_Symfony_Exception("Invalid configuration file provided; unknown config type '$suffix'");
        }
        $loader->load($file);
    }

    protected function _loadPath($path)
    {
        $loader = new LoSo_Symfony_Components_ServiceContainerLoaderAnnotations($this->getContainer());
        $loader->load($path);
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
