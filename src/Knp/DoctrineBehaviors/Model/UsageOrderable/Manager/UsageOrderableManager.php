<?php

namespace Knp\DoctrineBehaviors\Model\UsageOrderable\Manager;

use Doctrine\ORM\EntityManager;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


Class UsageOrderableManager
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ClassAnalyzer
     */
    protected $classAnalyzer;

    /**
     * @var trait
     */
    private $usageOrderableTrait;

    /**
     * @var trait
     */
    private $usageTimestampTrait;

    public function __construct(EventDispatcherInterface $dispatcher, EntityManager $em, ClassAnalyzer $classAnalyzer,
                                $usageOrderableTrait, $usageTimestampTrait)
    {
        $this->dispatcher = $dispatcher;
        $this->em = $em;
        $this->classAnalyzer = $classAnalyzer;
        $this->usageOrderableTrait = $usageOrderableTrait;
        $this->usageTimestampTrait = $usageTimestampTrait;
    }

    public function addUsageTimestamp($entity) {

        $entityMetadata = $this->em->getClassMetadata(get_class($entity));

        if ($this->isUsageOrderable($entityMetadata)) {

            $timestamp = $this->generateTimestamp($entity);
            $timestampMetadata = $this->em->getClassMetadata(get_class($timestamp));

            if ($this->isUsageTimestamp($timestampMetadata)) {
                
                $entity->addUsageTimestamp($timestamp);
                $timestamp->setUsageOrderable($entity);
                $this->save($entity);
            }
        }
    }

    /**
     *
     * @return UsageTimestamp
     */
    public function generateTimestamp($entity)
    {
        $timestamp = new \ReflectionClass($entity->getEntityName()."UsageTimestamp");
        return $timestamp->newInstance();
    }

    /**
     * @param $entity
     */
    public function save($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }


    /**
     * Checks if entity is UsageOrderable
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
}
