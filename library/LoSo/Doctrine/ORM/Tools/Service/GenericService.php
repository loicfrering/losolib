<?php
class LoSo_Doctrine_ORM_Tools_Service_GenericService
{
    public function setDao($dao)
    {
        $this->dao = $dao;
        return $this;
    }

    protected function getDao()
    {
        if(!$this->dao instanceof LoSo_Doctrine_ORM_Tools_Dao_GenericDao) {
            throw new LoSo_Exception('Dao property must be an instance of LoSo GenericDao when extending LoSo GenericService.');
        }
        return $this->dao;
    }

    public function create($entity)
    {
        $this->getDao()->create($entity);
    }

    public function update($entity)
    {
        $this->getDao()->update($entity);
    }

    public function delete($entity)
    {
        $this->getDao()->delete($entity);
    }

    public function flush()
    {
        $this->getDao()->flush();
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     *
     * @param string $alias
     * @return QueryBuilder $qb
     */
    public function createQueryBuilder($alias)
    {
        return $this->getDao()->createQueryBuilder($alias);
    }

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param $id The identifier.
     * @param int $hydrationMode The hydration mode to use.
     * @return object The entity.
     */
    public function find($id)
    {
        return $this->getDao()->find($id);
    }

    /**
     * Finds all entities in the repository.
     *
     * @param int $hydrationMode
     * @return array The entities.
     */
    public function findAll()
    {
        return $this->getDao()->findAll();
    }

    /**
     * Finds entities by a set of criteria.
     *
     * @param string $column
     * @param string $value
     * @return array
     */
    public function findBy(array $criteria)
    {
        return $this->getDao()->findBy($criteria);
    }

    /**
     * Finds a single entity by a set of criteria.
     *
     * @param string $column
     * @param string $value
     * @return object
     */
    public function findOneBy(array $criteria)
    {
        return $this->getDao()->findOneBy($criteria);
    }

    /**
     * Adds support for magic finders.
     *
     * @return array|object The found entity/entities.
     * @throws BadMethodCallException  If the method called is an invalid find* method
     *                                 or no find* method at all and therefore an invalid
     *                                 method call.
     */
    public function __call($method, $arguments)
    {
        if (substr($method, 0, 6) == 'findBy' || substr($method, 0, 9) == 'findOneBy') {
            return $this->getDao()->$method($arguments[0]);
        }

        throw new Exception("Undefined method '$method'.");
    }
}
