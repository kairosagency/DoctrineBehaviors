<?php

namespace Knp\DoctrineBehaviors\Model\UsageOrderable\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core;


Class UsageOrderableManager
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var SecurityContext
     */
    protected $security;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ClassAnalyzer
     */
    protected $classAnalyzer;

    /**
     * @var bool
     */
    protected $isRecursive;

    /**
     * @var trait
     */
    private $usageOrderableTrait;

    /**
     * @var trait
     */
    private $usageTimestampTrait;

    public function __construct(EventDispatcherInterface $dispatcher, SecurityContext $security, EntityManager $em, ClassAnalyzer $classAnalyzer, $isRecursive,
                                $usageOrderableTrait, $usageTimestampTrait)
    {
        $this->dispatcher = $dispatcher;
        $this->security = $security;
        $this->em = $em;
        $this->classAnalyzer = $classAnalyzer;
        $this->isRecursive = (bool) $isRecursive;
        $this->usageOrderableTrait = $usageOrderableTrait;
        $this->usageTimestampTrait = $usageTimestampTrait;
    }

    public function addUsageTimestamp($entity) {

        $entityMetadata = $this->em->getClassMetadata(get_class($entity));

        if ($this->isUsageOrderable($entityMetadata)) {

            $timestamp = $this->generateTimestamp($entity);
            $timestampMetadata = $this->em->getClassMetadata(get_class($timestamp));

            if ($this->isUsageTimestamp($timestampMetadata)) {

                $userId = null;
                if($user = $this->security->getToken()->getUser()) {
                    if($user->getId()) {
                        $userId = $user->getId();
                    }
                }

                $entity->addUsageTimestamp($timestamp);
                $timestamp->setUserId($userId)->setUsageOrderable($entity);
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

    /**
     * @return ClassAnalyzer
     */
    public function getClassAnalyzer()
    {
        return $this->classAnalyzer;
    }
}
