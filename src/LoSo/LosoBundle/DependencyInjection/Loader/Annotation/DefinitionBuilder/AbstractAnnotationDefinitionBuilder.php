<?php

namespace LoSo\LosoBundle\DependencyInjection\Loader\Annotation\DefinitionBuilder;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @author Loïc Frering <loic.frering@gmail.com>
 */
abstract class AbstractAnnotationDefinitionBuilder implements AnnotationDefinitionBuilderInterface
{
    protected $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }
}
