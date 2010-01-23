<?php
/**
 * Description of ServiceContainerFactory
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Symfony_Components_Autoloader
{
  /**
   * Registers sfServiceContainerAutoloader as an SPL autoloader.
   */
  static public function register()
  {
    ini_set('unserialize_callback_func', 'spl_autoload_call');
    spl_autoload_register(array(new self, 'autoload'));
  }

  /**
   * Handles autoloading of classes.
   *
   * @param  string  $class  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    if (0 !== strpos($class, 'sf'))
    {
      return false;
    }

    require 'Symfony/Components/'.$class.'.php';

    return true;
  }
}