<?php

namespace LoSo\Symfony\Component\DependencyInjection\Loader\Annotation;

use Doctrine\Common\Annotations\Annotation;

final class Service extends Annotation
{
    public $name;
    public $shared = true;
    public $factoryMethod;
    public $factoryService;
    public $tags = array();
}
