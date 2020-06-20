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

    /**
     * AnnotationReader constructor.
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Init the entity
     *
     * @param object $entity
     * @throws \ReflectionException
     */
    public function init($entity)
    {
        $this->entity = $entity;
        $class = new ReflectionClass(str_replace('Proxies\__CG__\\', '', get_class($entity)));
        $this->classAnnotation = $this->reader->getClassAnnotation($class, Loggable::class);
    }

    /**
     * Check if class or property is loggable
     *
     * @param null|string $property
     * @return bool
     */
    public function isLoggable($property = null)
    {
        return !$property ? $this->classAnnotation instanceof Loggable : $this->isPropertyLoggable($property);
    }

    /**
     * Check if propert is loggable
     *
     * @param $property
     * @return bool
     * @throws \ReflectionException
     */
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
