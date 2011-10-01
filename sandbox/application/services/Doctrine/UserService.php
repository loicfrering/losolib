<?php
use LoSo\LosoBundle\DependencyInjection\Annotations as DI;

/**
 * @DI\Service
 */
class Application_Service_Doctrine_UserService
{
    /**
     * @var Application_Repository_Doctrine_UserRepository
     * @DI\Inject
     */
    protected $userRepository;

    public function setUserRepository($userRepository)
    {
        $this->userRepository = $userRepository;
        return $this;
    }

    public function sayHello($id)
    {
        $user = $this->userRepository->find($id);
        if($user) {
            return 'Hello ' . $user->getFirstname() . ' ' . $user->getLastname() . '!';
        }

        return 'No user with id: ' . $id;
    }
}
