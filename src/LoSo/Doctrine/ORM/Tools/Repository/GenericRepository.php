<?php
abstract class LoSo_Doctrine_ORM_Tools_Repository_GenericRepository extends \Doctrine\ORM\EntityRepository
{
    public function __construct()
    {
        if(empty($this->entityName)) {
            throw new LoSo_Exception('EntityName must be defined when extending LoSo GenericRepository.');
        }
        $em = Zend_Registry::get(LoSo_Zend_Application_Bootstrap_SymfonyContainerBootstrap::getRegistryIndex())->em;
        $metadata = $em->getClassMetadata($this->entityName);
        parent::__construct($em, $metadata);
    }

    public function getEntityName()
    {
        return $this->entityName;
    }

    public function populate($entity, array $values)
    {
        foreach ($values as $key => $value) {
            $method = 'set' . ucfirst($key);
            if(!is_array($value) && method_exists($entity, $method)) {
                $entity->$method($value);
            }
        }
    }

    public function create($entity)
    {
        $this->_em->persist($entity);
    }

    public function update($entity)
    {
        $this->_em->merge($entity);
    }

    public function delete($entity)
    {
        $this->_em->remove($entity);
    }

    public function flush()
    {
        $this->_em->flush();
    }

    public function clear()
    {
        $this->_em->clear();
    }
}
