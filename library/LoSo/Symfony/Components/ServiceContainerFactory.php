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
