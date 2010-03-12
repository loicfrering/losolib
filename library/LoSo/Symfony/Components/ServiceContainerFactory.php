<?php
/**
 * Description of ServiceContainerFactory
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Symfony_Components_ServiceContainerFactory
{
    protected static $_container;

    public static function getContainer(array $options)
    {
        $doCache = isset($options['cache']) ? (bool) $options['cache'] : false;
        if(isset($options['cacheFile'])) {
            $cacheFile = $options['cacheFile'];
            $cacheName = pathinfo($cacheFile, PATHINFO_FILENAME);
        }
        else {
            $cacheFile = sys_get_temp_dir() . '/AppServiceContainer.php';
            $cacheName = 'AppServiceContainer';
        }

        if ($doCache && file_exists($cacheFile)) {
            require_once $cacheFile;
            self::$_container = new $cacheName();
        }
        else {
            self::$_container = new sfServiceContainerBuilder();
            if(isset($options['configFiles'])) {
                foreach($options['configFiles'] as $file) {
                    self::_loadConfigFile($file);
                }
            }
            if(isset($options['configPaths'])) {
                foreach($options['configPaths'] as $path) {
                    self::_loadPath($path);
                }
            }

            if($doCache) {
                $dumper = new sfServiceContainerDumperPhp(self::$_container);
                file_put_contents($cacheFile, $dumper->dump(array('class' => $cacheName)));
            }
        }

        return self::$_container;
    }

    protected static function _loadConfigFile($file)
    {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'xml':
                $loader = new sfServiceContainerLoaderFileXml(self::$_container);
                break;

            case 'yml':
                $loader = new sfServiceContainerLoaderFileYaml(self::$_container);
                break;

            case 'ini':
                $loader = new sfServiceContainerLoaderFileIni(self::$_container);
                break;

            default:
                throw new LoSo_Symfony_Exception("Invalid configuration file provided; unknown config type '$suffix'");
        }
        $loader->load($file);
    }

    protected static function _loadPath($path)
    {
        $loader = new LoSo_Symfony_Components_ServiceContainerLoaderAnnotations(self::$_container);
        $loader->load($path);
    }
}
