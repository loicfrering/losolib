<?php
use LoSo\LosoBundle\DependencyInjection\Annotations as DI;

/**
 * @DI\Controller
 */
class UserController extends LoSo_Zend_Controller_Action
{
    /**
     * @var Application_Repository_Doctrine_UserRepository
     * @DI\Inject
     */
    protected $userRepository;

    public function setUserRepository($userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @var Application_Service_Doctrine_UserService
     * @DI\Inject
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
                $this->userRepository->populate($user, $userForm->getValues());
                $this->userRepository->create($user);
                $this->userRepository->flush();

                $userForm->reset();
            }
        }

        $this->view->users = $this->userRepository->findAll();
        $this->view->userForm = $userForm;
    }

    public function helloAction()
    {
        $hello = $this->userService->sayHello($this->_getParam('id'));
        $this->view->hello = $hello;
    }
}
