<?php

namespace Mb\DoctrineLogBundle\Service;

use Mb\DoctrineLogBundle\Annotation\Exclude;
use Mb\DoctrineLogBundle\Annotation\Log;
use Mb\DoctrineLogBundle\Annotation\Loggable;
use ReflectionClass;
use Doctrine\Common\Annotations\Reader;

/**
 * Class AnnotationReader
 * @package Mb\DoctrineLogBundle\Service
 */
class AnnotationReader
{
    /**
     * @var Reader $reader
     */
    private $reader;

    /**
     * @var Loggable
     */
    private $classAnnotation;

    /**
     * @var object
     */
    private $entity;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function init($entity)
    {
        $this->entity = $entity;
        $class = new ReflectionClass(str_replace('Proxies\__CG__\\', '', get_class($entity)));
        $this->classAnnotation = $this->reader->getClassAnnotation($class, Loggable::class);
    }

    public function isLoggable($property = null)
    {
        return !$property ? $this->classAnnotation instanceof Loggable : $this->isPropertyLoggable($property);
    }

    private function isPropertyLoggable($property)
    {
        $property = new \ReflectionProperty(
            str_replace('Proxies\__CG__\\', '', get_class($this->entity)),
            $property
        );

        if ($this->classAnnotation->strategy === Loggable::STRATEGY_EXCLUDE_ALL) {
            // check for log annotation
            $annotation = $this->reader->getPropertyAnnotation($property, Log::class);

            return $annotation instanceof Log;
        }

        // include all strategy, check for exclude
        $annotation = $this->reader->getPropertyAnnotation($property, Exclude::class);

        return !$annotation instanceof Exclude;
    }
}
