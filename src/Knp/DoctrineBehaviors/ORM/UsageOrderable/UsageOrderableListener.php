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

use Knp\DoctrineBehaviors\Model\UsageOrderable\UsageOrderable;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractListener;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\ORM\Event\LifecycleEventArgs,
    Doctrine\ORM\Events;



class UserOrderableListener extends AbstractListener
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

        if ($this->isUserOrderable($classMetadata)) {
            $this->mapUserOrderable($classMetadata);
        }

        if ($this->isUsageTimestamp($classMetadata)) {
            $this->mapUsageTimestamp($classMetadata);
        }
    }

    private function mapTranslatable(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('translations')) {
            $classMetadata->mapOneToMany([
                'fieldName'     => 'usageTimestamps',
                'mappedBy'      => '$usageOrderable',
                'indexBy'       => 'date',
                'cascade'       => ['persist', 'merge', 'remove'],
                'targetEntity'  => $classMetadata->name.'UsageTimestamp',
                'orphanRemoval' => true
            ]);
        }
    }

    private function mapTranslation(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('translatable')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => '$usageOrderable',
                'inversedBy'   => 'usageTimestamps',
                'joinColumns'  => [[
                    'name'                 => 'usage_orderable_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'CASCADE'
                ]],
                'targetEntity' => substr($classMetadata->name, 0, -11)
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
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $this->usageTimestamp, $this->isRecursive);
    }



    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if($this->isUsageOrderable($classMetadata)) {
            $entity->incrementUsage();
            $em->persist($entity);
            $em->flush();
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
            Events::postLoad,
        ];
    }
}
