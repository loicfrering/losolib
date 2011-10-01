<?php

namespace LoSo\LosoBundle\DependencyInjection\Loader\Annotation\DefinitionBuilder;

interface AnnotationDefinitionBuilderInterface
{
    public function build(\ReflectionClass $reflClass, $annot);
}
