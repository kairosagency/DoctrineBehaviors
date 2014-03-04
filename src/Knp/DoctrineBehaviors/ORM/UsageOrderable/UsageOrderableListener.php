<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\UsageOrderable;


use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractListener;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\ORM\Event\PreUpdateEventArgs,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;



class UsageOrderableListener extends AbstractListener
{
    private $usageOrderableTrait;
    private $usageTimestampTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive,
                                $usageOrderableTrait, $usageTimestampTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->usageOrderableTrait = $usageOrderableTrait;
        $this->usageTimestampTrait = $usageTimestampTrait;
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isUsageOrderable($classMetadata)) {
            $this->mapUsageOrderable($classMetadata);
        }

        if ($this->isUsageTimestamp($classMetadata)) {
            $this->mapUsageTimestamp($classMetadata);
        }
    }

    private function mapUsageOrderable(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('usageTimestamps')) {
            $classMetadata->mapOneToMany([
                'fieldName'     => 'usageTimestamps',
                'mappedBy'      => 'usageOrderable',
                'indexBy'       => 'date',
                'cascade'       => ['persist', 'merge', 'remove'],
                'targetEntity'  => $classMetadata->name.'UsageTimestamp',
                'orphanRemoval' => true
            ]);
        }
    }

    private function mapUsageTimestamp(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('usageOrderable')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'usageOrderable',
                'inversedBy'   => 'usageTimestamps',
                'joinColumns'  => [[
                    'name'                 => 'usageorderable_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'CASCADE'
                ]],
                'targetEntity' => substr($classMetadata->name, 0, -14)
            ]);
        }
    }

    /**
     * Checks if entity is translatable
     *
     * @param ClassMetadata $classMetadata
     * @param bool          $isRecursive   true to check for parent classes until found
     *
     * @return boolean
     */
    private function isUsageOrderable(ClassMetadata $classMetadata, $isRecursive = false)
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $this->usageOrderableTrait, $this->isRecursive);
    }

    /**
     * @param  ClassMetadata $classMetadata
     * @return boolean
     */
    private function isUsageTimestamp(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $this->usageTimestampTrait, $this->isRecursive);
    }


    /**
     * @param PreUpdateEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $classMetadata = $em->getClassMetadata(get_class($entity));
            if ($this->isUsageOrderable($classMetadata)) {
                $timestamp = $entity->generateTimestamp();
                $timestampMd = $em->getClassMetadata(get_class($timestamp));
                if ($this->isUsageTimestamp($timestampMd)) {

                    $entity->addUsageTimestamp($timestamp);
                    $timestamp->setUsageOrderable($entity);
                    $em->persist($entity);
                    $em->persist($timestamp);

                    $uow->computeChangeSet($classMetadata, $entity);
                    $uow->computeChangeSet($timestampMd, $timestamp);
                }
            }
        }
    }


    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::onFlush,
        ];
    }
}
