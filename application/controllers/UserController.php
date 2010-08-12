<?php
/**
 * @Controller
 */
class UserController extends LoSo_Zend_Controller_Action
{
    /**
     * @var Application_Service_Doctrine_UserService
     * @Inject
     */
    protected $userService;

    public function setUserService($userService)
    {
        $this->userService = $userService;
    }

    public function indexAction()
    {
        $userForm = new Application_Form_User();

        if($this->getRequest()->isPost()) {
            if($userForm->isValid($_POST)) {
                $user = new Application_Model_User();
                $this->userService->populate($user, $userForm->getValues());
                $this->userService->create($user);
                $this->userService->flush();

                $userForm->reset();
            }
        }

        $this->view->users = $this->userService->findAll();
        $this->view->userForm = $userForm;
    }

    public function helloAction()
    {
        $hello = $this->userService->sayHello($this->_getParam('id'));
        $this->view->hello = $hello;
    }
}
