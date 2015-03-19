<?php
namespace Conjecto\RAL\Framing\Metadata\Driver;

use Conjecto\RAL\Framing\Metadata\ClassMetadata;
use Conjecto\RAL\Framing\Metadata\MethodMetadata;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Metadata\Driver\AnnotationDriver as BaseAnnotationDriver;
use Metadata\Driver\DriverInterface;
use Metadata\PropertyMetadata;

/**
 * Extending AnnotationDriver to handle JsonLD options
 *
 * @package Conjecto\RAL\Bundle\Serializer\Metadata\Driver;
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param \ReflectionClass $class
     * @return ClassMetadata
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new ClassMetadata($class->getName());
        $annotation = $this->reader->getClassAnnotation($class, 'Conjecto\\RAL\\Framing\\Annotation\\JsonLd');
        if(null !== $annotation) {
            // frame
            $classMetadata->setFrame($annotation->frame);
            // options
            $classMetadata->setOptions($annotation->options);
        }

        foreach ($class->getMethods() as $reflectionMethod) {
            $methodMetadata = new MethodMetadata($class->getName(), $reflectionMethod->getName());
            $annotation = $this->reader->getMethodAnnotation(
              $reflectionMethod,
              'Conjecto\\RAL\\Framing\\Annotation\\JsonLd'
            );
            if (null !== $annotation) {
                $methodMetadata->setFrame($annotation->frame);
                $methodMetadata->setOptions($annotation->options);
            }
            $classMetadata->addMethodMetadata($methodMetadata);
        }

        return $classMetadata;
    }
}