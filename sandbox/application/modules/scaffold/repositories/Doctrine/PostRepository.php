<?php
use LoSo\LosoBundle\DependencyInjection\Annotations as DI;

/**
 * @DI\Service postRepository
 */
class Scaffold_Repository_Doctrine_PostRepository extends LoSo_Doctrine_ORM_Tools_Repository_GenericRepository
{
    protected $entityName = 'Scaffold_Model_Post';

}
