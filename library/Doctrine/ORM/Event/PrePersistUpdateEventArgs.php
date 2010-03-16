<?php

namespace Doctrine\ORM\Event;

/**
 * Class that holds event arguments for a preInsert/preUpdate event.
 *
 * @author Roman Borschel <roman@code-factory.org>
 * @since 2.0
 */
class PrePersistUpdateEventArgs extends LifecycleEventArgs
{
    private $_entityChangeSet;

    public function __construct($entity, array $changeSet)
    {
        parent::__construct($entity);
        $this->_entityChangeSet = $changeSet;
    }
    
    public function getEntityChangeSet()
    {
    	return $this->_entityChangeSet;
    }
}

