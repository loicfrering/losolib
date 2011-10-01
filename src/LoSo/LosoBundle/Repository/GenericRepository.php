<?php

namespace LoSo\LosoBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GenericRepository extends EntityRepository
{
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

    public function populate($entity, array $values)
    {
        foreach ($values as $key => $value) {
            $method = 'set' . ucfirst($key);
            if(!is_array($value) && method_exists($entity, $method)) {
                $entity->$method($value);
            }
        }
    }
}
