<?php

namespace Mb\DoctrineLogBundle\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use JMS\Serializer\SerializerInterface as Serializer;

use Mb\DoctrineLogBundle\Service\AnnotationReader;
use Mb\DoctrineLogBundle\Service\Logger as LoggerService;
use Mb\DoctrineLogBundle\Entity\Log as LogEntity;
use Mb\DoctrineLogBundle\Annotation\Loggable;
use Psr\Log\LoggerInterface;

/**
 * Class Logger
 * @package CoreBundle\EventListener
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter.Unused)
 */
class Logger
{
    /**
     * @var array
     */
    protected $logs;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerService
     */
    private $loggerService;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var LoggerInterface
     */
    private $monolog;

    /**
     * @var array
     */
    private $ignoreProperties;

    /**
     * Logger constructor.
     * @param EntityManagerInterface $em
     * @param LoggerService          $loggerService
     * @param Serializer             $serializer
     * @param AnnotationReader       $reader
     * @param array                  $ignoreProperties
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerService $loggerService,
        Serializer $serializer,
        AnnotationReader $reader,
        LoggerInterface $monolog,
        array $ignoreProperties
    ) {
        $this->em = $em;
        $this->loggerService = $loggerService;
        $this->serializer = $serializer;
        $this->reader = $reader;
        $this->ignoreProperties = $ignoreProperties;
        $this->monolog = $monolog;
    }

    /**
     * Post persist listener
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $this->log($entity, LogEntity::ACTION_CREATE);
    }

    /**
     * Post update listener
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $this->log($entity, LogEntity::ACTION_UPDATE);

    }

    /**
     * Pre remove listener
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $this->log($entity, LogEntity::ACTION_REMOVE);

    }

    /**
     * Flush logs. Can't flush inside post update
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!empty($this->logs)) {
            foreach ($this->logs as $log) {
                $this->em->persist($log);
            }

            $this->logs = [];
            $this->em->flush();
        }
    }

    /**
     * Log the action
     *
     * @param object $entity
     * @param string $action
     */
    private function log($entity, $action)
    {
        try {
            $this->reader->init($entity);
            if ($this->reader->isLoggable()) {
                $changes = null;
                if ($action === LogEntity::ACTION_UPDATE) {
                    $uow = $this->em->getUnitOfWork();

                    // get changes => should be already computed here (is a listener)
                    $changeSet = $uow->getEntityChangeSet($entity);
                    // if we have no changes left => don't create revision log
                    if (count($changeSet) == 0) {
                        return;
                    }

                    // just getting the changed objects ids
                    foreach ($changeSet as $key => &$values) {
                        if (in_array($key, $this->ignoreProperties) || !$this->reader->isLoggable($key)) {
                            // ignore configured properties
                            unset($changeSet[$key]);
                        }

                        if (is_object($values[0]) && method_exists($values[0], 'getId')) {
                            $values[0] = $values[0]->getId();
                        }

                        if (is_object($values[1]) && method_exists($values[1], 'getId')) {
                            $values[1] = $values[1]->getId();
                        }
                    }

                    if (!empty($changeSet)) {
                        $changes = $this->serializer->serialize($changeSet, 'json');
                    }
                }

                if ($action === LogEntity::ACTION_UPDATE && !$changes) {
                    // Log nothing
                } else {
                    $this->logs[] = $this->loggerService->log(
                        $entity,
                        $action,
                        $changes
                    );
                }
            }
        } catch (\Exception $e) {
            $this->monolog->error($e->getMessage());
        }
    }
}
