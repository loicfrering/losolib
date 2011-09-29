<?php

namespace LoSo\LosoBundle\DependencyInjection\Annotations;

use Doctrine\Common\Annotations\Annotation;

final class Repository extends Annotation
{
    public $entity;
    public $entityManager;
    public $name;
}
