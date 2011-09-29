<?php
use LoSo\LosoBundle\DependencyInjection\Annotations as DI;

/**
 * @DI\Service
 */
class Application_Repository_Doctrine_UserRepository extends LoSo_Doctrine_ORM_Tools_Repository_GenericRepository
{
    protected $entityName = 'Application_Model_User';

}
