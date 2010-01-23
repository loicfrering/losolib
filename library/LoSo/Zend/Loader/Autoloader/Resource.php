<?php
/**
 * Description of LoSo_Zend_Loader_Autoloader_Resource
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Loader_Autoloader_Resource extends Zend_Loader_Autoloader_Resource
{
    protected $_container;

    public function  __construct($container, $options)
    {
        $this->_container = $container;
        parent::__construct($options);
    }

    public function addResourceType($type, $path, $namespace = null)
    {
        parent::addResourceType($type, $path, $namespace);
        $loader = new LoSo_Symfony_Components_ServiceContainerLoaderAnnotations($this->_container);
        $loader->load($this->getBasePath() . '/' . $path);
    }
}
