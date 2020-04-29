<?php

namespace Mb\DoctrineLogBundle\EventListener;

use ReflectionClass;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use JMS\Serializer\SerializerInterface as Serializer;

use Mb\DoctrineLogBundle\Service\Logger as LoggerService;
use Mb\DoctrineLogBundle\Entity\Log as LogEntity;
use Mb\DoctrineLogBundle\Annotation\Loggable;

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
     * @var Reader $reader
     */
    private $reader;

    /**
     * @var array
     */
    private $ignoreProperties;

    /**
     * Logger constructor.
     * @param EntityManagerInterface $em
     * @param LoggerService          $loggerService
     * @param Serializer             $serializer
     * @param Reader                 $reader
     * @param array                  $ignoreProperties
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerService $loggerService,
        Serializer $serializer,
        Reader $reader,
        array $ignoreProperties
    ) {
        $this->em = $em;
        $this->loggerService = $loggerService;
        $this->serializer = $serializer;
        $this->reader = $reader;
        $this->ignoreProperties = $ignoreProperties;
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
            $class = new ReflectionClass($entity);
            $annotation = $this->reader->getClassAnnotation($class, Loggable::class);
            if ($annotation instanceof Loggable) {
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
                        if (in_array($key, $this->ignoreProperties)) {
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

                    $changes = $this->serializer->serialize($changeSet, 'json');
                }

                $this->logs[] = $this->loggerService->log(
                    $entity,
                    $action,
                    $changes
                );
            }
        } catch (\Exception $e) {
            // todo: log error
        }
    }
}
