<?php
/**
 * Description of DiController
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class DiController extends Zend_Controller_Action
{
    /**
     * @Inject
     * @var string
     */
    private $_testService;

    public function indexAction()
    {
        $container = $this->getInvokeArg('bootstrap')->getContainer();
        $testService = $container->testService;

        $this->view->parameters = $container->getParameters();
        $this->view->serviceIds = $container->getServiceIds();
        $this->view->testServiceOut1 = $testService->test();
        $this->view->testServiceOut2 = $this->_testService->test();
    }
}
