<?php
/**
 * Provide bootstrapping utilities to TestCase classes not dedicated to controllers.
 * Allows to test classes in a multi-layered architecture.
 *
 * @category   Zend
 * @package    LoSo_Zend
 * @subpackage Test
 * @author     Loïc Frering <loic.frering@gmail.com>
 */
abstract class LoSo_Zend_Test_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Zend_Application instance.
     *
     * @var Zend_Application
     */
    protected $_application;

    /**
     * Bootstrap application.
     */
    public function bootstrap()
    {
        $this->getApplication()->bootstrap();
    }

    /**
     * Execute bootstrap prior test launch.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->bootstrap();
    }

    /**
     * Retrieve Zend_Application instance.
     *
     * @return Zend_Application
     */
    public function getApplication()
    {
        if(null === $this->_application) {
            $this->_application = new Zend_Application(
                'testing',
                APPLICATION_PATH . '/configs/application.ini'
            );
        }
        return $this->_application;
    }

    /**
     * Set Zend_Application instance.
     *
     * @param  Zend_Application
     * @return LoSo_Zend_Test_TestCase
     */
    public function setApplication($application)
    {
        $this->_application = $application;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }
}
