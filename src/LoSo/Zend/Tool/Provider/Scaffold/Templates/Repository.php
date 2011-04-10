<?php
/**
 * @Service {%entityVar}Repository
 */
class {%moduleNamespace}_Repository_Doctrine_{%entity}Repository extends LoSo_Doctrine_ORM_Tools_Repository_GenericRepository
{
    protected $entityName = '{%moduleNamespace}_Model_{%entity}';

}
