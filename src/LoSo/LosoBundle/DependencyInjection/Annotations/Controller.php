<?php

namespace LoSo\LosoBundle\DependencyInjection\Annotations;

use Doctrine\Common\Annotations\Annotation;

final class Controller extends Annotation
{
    public $name;
}
