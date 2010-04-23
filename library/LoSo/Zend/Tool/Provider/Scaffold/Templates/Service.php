<?php
/**
 * @Service
 */
class {%moduleNamespace}_Service_Doctrine_{%entity}Service extends LoSo_Doctrine_ORM_Tools_Service_GenericService
{
    /**
     * @var {%moduleNamespace}_Dao_{%entity}
     * @Inject {%entityVar}Dao
     */
    protected $dao;
}
