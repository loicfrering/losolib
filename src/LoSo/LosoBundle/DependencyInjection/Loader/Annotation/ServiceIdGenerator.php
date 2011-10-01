<?php

namespace LoSo\LosoBundle\DependencyInjection\Loader\Annotation;

/**
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class ServiceIdGenerator
{
    public function generate(\ReflectionClass $reflClass)
    {
        $className = $reflClass->getName();
        if (false !== ($pos = strrpos($className, '_'))) {
            $id = lcfirst(substr($className, $pos + 1));
        } else if (false !== ($pos = strrpos($className, '\\'))) {
            $id = lcfirst(substr($className, $pos + 1));
        } else {
            $id = lcfirst($className);
        }

        return $id;
    }
}
