<?php

namespace Mb\DoctrineLogBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

use Mb\DoctrineLogBundle\Entity\Log as LogEntity;

/**
 * Class Logger
 * @package Mb\DoctrineLogBundle\Service
 */
class Logger
{
    /**
     * @var EntityManagerInterface $em
     */
    protected $em;

    /**
     * Logger constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Logs object change
     *
     * @param object $object
     * @param string $action
     * @param string $changes
     * @return LogEntity
     */
    public function log($object, $action, $changes = null) : LogEntity
    {
        $log = new LogEntity();
        $log
            ->setObjectClass(str_replace('Proxies\__CG__\\', '', get_class($object)))
            ->setForeignKey($object->getId())
            ->setAction($action)
            ->setChanges($changes)
        ;

        return $log;
    }

    /**
     * Saves a log
     *
     * @param LogEntity $log
     * @return bool
     */
    public function save(LogEntity $log) : bool
    {
        $this->em->persist($log);
        $this->em->flush();

        return true;
    }
}
