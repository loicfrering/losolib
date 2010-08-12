<?php
/**
 * @Service
 */
class Application_Service_Doctrine_UserService extends LoSo_Doctrine_ORM_Tools_Service_GenericService
{
    /**
     * @var Application_Dao_User
     * @Inject("userDao")
     */
    protected $dao;

    public function sayHello($id)
    {
        $user = $this->find($id);
        if($user) {
            return 'Hello ' . $user->getFirstname() . ' ' . $user->getLastname() . '!';
        }

        return 'No user with id: ' . $id;
    }
}
