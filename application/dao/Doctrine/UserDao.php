<?php
/**
 * @Service userDao
 */
class Application_Dao_Doctrine_UserDao extends LoSo_Doctrine_ORM_Tools_Dao_GenericDao
{
    protected $entityName = 'Application_Model_User';

}
