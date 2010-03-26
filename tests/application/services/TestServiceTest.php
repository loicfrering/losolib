<?php
/**
 * Description of TestServiceTest
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class TestServiceTest extends LoSo_Zend_Test_SymfonyContainerAwareTestCase
{
    /**
     * @var Application_Service_TestService
     * @Inject
     */
    protected $_testService;

    public function testTest2()
    {
        $this->assertContains('called from testService', $this->_testService->test2());
    }
}

