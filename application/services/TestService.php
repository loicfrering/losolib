<?php
/**
 * Description of Default_Service_TestService
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 * @Service
 */
class Default_Service_TestService
{
    /**
     * @var Default_Service_TestService2
     */
    protected $_testService2;

    /**
     * @param Default_Service_TestService2 $testService2
     * @return Default_Service_TestService
     * @Inject
     */
    public function setTestService2($testService2)
    {
        $this->_testService2 = $testService2;
        return $this;
    }

    public function test()
    {
        return 'Test method from TestService';
    }

    public function test2()
    {
        return $this->_testService2->test2() . ' called from testService';
    }
}
