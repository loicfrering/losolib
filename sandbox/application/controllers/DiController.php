<?php
use LoSo\LosoBundle\DependencyInjection\Annotations as DI;

/**
 * Description of DiController
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 * @DI\Controller
 */
class DiController extends LoSo_Zend_Controller_Action
{
    /**
     * @var Application_Service_TestService
     *
     * @DI\Inject
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
        $testService = $container->get('testService');

        $this->view->parameters = $container->getParameterBag()->all();
        $this->view->serviceIds = $container->getServiceIds();
        $this->view->testServiceOut1 = $testService->test();
        $this->view->testServiceOut2 = $this->_testService->test2();
    }
}
