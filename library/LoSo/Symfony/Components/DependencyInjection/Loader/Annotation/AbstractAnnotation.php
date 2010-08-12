<?php

namespace LoSo\Symfony\Components\DependencyInjection\Loader\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Description of AbstractAnnotation
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
abstract class AbstractAnnotation extends Annotation
{
    abstract public function defineFromConstructor($constructor, $definition);
    abstract public function defineFromProperty($property, $definition);
    abstract public function defineFromMethod($method, $definition);

    protected function filterUnderscore($value)
    {
        if(strpos($value, '_') === 0) {
            return substr($value, 1);
        }
        return $value;
    }

    protected function filterSetPrefix($value)
    {
        if(strpos($value, 'set') === 0) {
            return lcfirst(substr($value, 3));
        }
        return lcfirst($value);
    }
}
