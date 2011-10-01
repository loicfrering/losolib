<?php
use LoSo\LosoBundle\DependencyInjection\Annotations as DI;
use LoSo\LosoBundle\Repository\GenericRepository;

/**
 * @DI\Repository("Application_Model_User")
 */
class Application_Repository_Doctrine_UserRepository extends GenericRepository
{
    protected $entityName = 'Application_Model_User';

}
