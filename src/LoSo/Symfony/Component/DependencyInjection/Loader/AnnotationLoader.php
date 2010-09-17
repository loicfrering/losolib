<?php

namespace LoSo\Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\DependencyInjection\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * AnnotationLoader loads annotated class service definitions.
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 */
class AnnotationLoader extends Loader
{
    protected $reader;
    protected $annotations = array();

    public function  __construct(ContainerBuilder $container)
    {
        parent::__construct($container);
        $this->annotations = array(
            'LoSo\Symfony\Component\DependencyInjection\Loader\Annotation\Inject',
            'LoSo\Symfony\Component\DependencyInjection\Loader\Annotation\Value'
        );
        $this->reader = new AnnotationReader();
        $this->reader->setDefaultAnnotationNamespace('LoSo\Symfony\Component\DependencyInjection\Loader\Annotation\\');
        //$this->reader->setAutoloadAnnotations(true);
        require_once __DIR__ . '/Annotation/Service.php';
        require_once __DIR__ . '/Annotation/Inject.php';
        require_once __DIR__ . '/Annotation/Value.php';
    }
    
    public function load($path)
    {
        try {
            $includedFiles = array();
            $directoryIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            foreach($directoryIterator as $fileInfo) {
                if($fileInfo->isFile()) {
                    $suffix = strtolower(pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION));
                    if($suffix == 'php') {
                        $sourceFile = realpath($fileInfo->getPathName());
                        require_once $sourceFile;
                        $includedFiles[] = $sourceFile;
                    }
                }
            }

            $declaredClasses = get_declared_classes();
            foreach($declaredClasses as $className) {
                $reflClass = new \ReflectionClass($className);
                if(in_array($reflClass->getFileName(), $includedFiles)) {
                    $this->reflectDefinition($reflClass);
                }
            }
        }
        catch(UnexpectedValueException $e) {
            
        }
    }

    public function supports($resource)
    {
        return is_dir($resource);
    }

    protected function reflectDefinition($reflClass)
    {
        $definition = new Definition($reflClass->getName());

        if ($annot = $this->reader->getClassAnnotation($reflClass, 'LoSo\Symfony\Component\DependencyInjection\Loader\Annotation\Service')) {
            $id = $this->extractServiceName($reflClass, $annot);

            if (isset($annot->shared)) {
                $definition->setShared($annot->shared);
            }

            if (isset($annot->factoryMethod)) {
                $definition->setFactoryMethod($annot->factoryMethod);
            }

            if (isset($annot->factoryService)) {
                $definition->setFactoryService($annot->factoryService);
            }

            foreach ($annot->tags as $tag) {
                $name = $tag['name'];
                unset($tag['name']);

                $definition->addTag($name, $tag);
            }

            $this->reflectProperties($reflClass, $definition);
            $this->reflectMethods($reflClass, $definition);
            $this->reflectConstructor($reflClass, $definition);

            $this->container->setDefinition($id, $definition);
        }
    }

    protected function reflectProperties($reflClass, $definition)
    {
        foreach ($reflClass->getProperties() as $property) {
            foreach ($this->annotations as $annotClass) {
                if ($annot = $this->reader->getPropertyAnnotation($property, $annotClass)) {
                    $annot->defineFromProperty($property, $definition);
                }
            }
        }
    }

    protected function reflectMethods($reflClass, $definition)
    {
        foreach ($reflClass->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() == $reflClass->getName() && strpos($method->getName(), 'set') === 0) {
                foreach ($this->annotations as $annotClass) {
                    if ($annot = $this->reader->getMethodAnnotation($method, $annotClass)) {
                        $annot->defineFromMethod($method, $definition);
                    }
                }
            }
        }
    }

    protected function reflectConstructor($reflClass, $definition)
    {
        try {
            $constructor = $reflClass->getMethod('__construct');
            foreach ($this->annotations as $annotClass) {
                if ($annot = $this->reader->getMethodAnnotation($constructor, $annotClass)) {
                    $annot->defineFromConstructor($constructor, $definition);
                }
            }
        } catch (\ReflectionException $e) {

        }
    }

    protected function extractServiceName($reflClass, $annot)
    {
        $serviceName = $annot->value ?: $annot->name;
        
        if (null === $serviceName) {
            $className = $reflClass->getName();
            if (false !== ($pos = strrpos($className, '_'))) {
                $serviceName = lcfirst(substr($className, $pos + 1));
            } else if (false !== ($pos = strrpos($className, '\\'))) {
                $serviceName = lcfirst(substr($className, $pos + 1));
            } else {
                $serviceName = lcfirst($className);
            }
        }

        return $serviceName;
    }
}
