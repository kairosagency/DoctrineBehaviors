<?php

namespace Knp\DoctrineBehaviors\Model\UsageOrderable;

use Doctrine\Common\Collections\ArrayCollection;

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
     * Returns entity class name.
     *
     * @return string
     */
    public static function getEntityName()
    {
        return  __CLASS__;
    }
}
