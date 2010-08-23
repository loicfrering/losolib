<?php

namespace LoSo\Symfony\Components\DependencyInjection;

use Symfony\Components\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;

/**
 * Add magic methods support to Symfony's ContainerBuilder.
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class ContainerBuilder extends SymfonyContainerBuilder
{
    /**
     * Returns true if the container has a service with the given identifier.
     *
     * @param  string  The service identifier
     *
     * @return boolean true if the container has a service with the given identifier, false otherwise
     */
    public function __isset($id)
    {
        return $this->has($id);
    }

    /**
     * Gets the service associated with the given identifier.
     *
     * @param  string The service identifier
     *
     * @return mixed  The service instance associated with the given identifier
     */
    public function __get($id)
    {
        return $this->get($id);
    }

    /**
     * Sets a service.
     *
     * @param string The service identifier
     * @param mixed  A service instance
     */
    public function __set($id, $service)
    {
        $this->set($id, $service);
    }

}
