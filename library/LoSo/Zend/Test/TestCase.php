<?php
/**
 * Description of LoSo_Zend_Test_TestCase
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class LoSo_Zend_Test_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_Application
     */
    protected $_application;

    public function bootstrap()
    {
        $this->getApplication()->bootstrap();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->bootstrap();
    }

    /**
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

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }
}
