<?php
/**
 * Description of DiController
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 * @Service
 */
class DiController extends LoSo_Zend_Controller_Action
{
    /**
     * @var Application_Service_TestService
     * @Inject
     */
    private $_testService;

    public function setTestService($testService)
    {
        $this->_testService = $testService;
        return $this;
    }

    public function indexAction()
    {
        $container = $this->getInvokeArg('bootstrap')->getContainer();
        $testService = $container->testService;

        $this->view->parameters = $container->getParameters();
        $this->view->serviceIds = $container->getServiceIds();
        $this->view->testServiceOut1 = $testService->test();
        $this->view->testServiceOut2 = $this->_testService->test2();
    }
}
