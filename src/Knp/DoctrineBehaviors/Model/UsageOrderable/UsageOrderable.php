<?php

namespace Knp\DoctrineBehaviors\Model\UsageOrderable;

use Doctrine\Common\Collections\ArrayCollection;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Doctrine\ORM\Event\LifeCycleEventArgs;

trait UsageOrderable
{

    /**
     * @var UsageTimestamp
     */
    protected $usageTimestamps;

    /**
     * Returns collection of translations.
     *
     * @return ArrayCollection
     */
    public function getUsageTimestamp()
    {
        return $this->usageTimestamps = $this->usageTimestamps ?: new ArrayCollection();
    }

    /**
     * Adds new translation.
     *
     * @param UsageTimestamp $usageTimestamp
     */
    public function addUsageTimestamp($usageTimestamp)
    {
        $this->getUsageTimestamp()->add($usageTimestamp);
        $usageTimestamp->setUsageOrderable($this);
    }

    /**
     * Removes specific translation.
     *
     * @param UsageTimestamp $usageTimestamp
     */
    public function removeUsageTimestamp($usageTimestamp)
    {
        $this->getUsageTimestamp()->removeElement($usageTimestamp);
    }

    /**
     *
     * @return UsageOrderable
     */
    public function incrementUsageTimestamp()
    {
        $timestamp = $this->generateTimestamp();
        $this->addUsageTimestamp($timestamp);
        $timestamp->setUsageOrderable($this);
    }

    /**
     * Returns entity class name.
     *
     * @return string
     */
    public static function getEntityName()
    {
        return  __CLASS__;
    }
}
