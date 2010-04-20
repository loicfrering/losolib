<?php
/**
 * @Service
 */
class Application_Service_Doctrine_PostService extends LoSo_Doctrine_ORM_Tools_Service_GenericService
{
    /**
     * @var Application_Dao_Post
     * @Inject postDao
     */
    protected $dao;
}
