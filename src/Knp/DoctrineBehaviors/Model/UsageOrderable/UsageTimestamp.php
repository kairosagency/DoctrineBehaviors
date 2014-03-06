<?php

namespace Knp\DoctrineBehaviors\Model\UsageOrderable;

trait UsageTimestamp
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Datetime
     *
     * @ORM\Column(type="datetime")
     */
    protected $datetime;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    protected $count;

    /**
     * @var int
     *
     * @ORM\Column(type="int")
     */
    protected $userid;

    /**
     * @var UsageOrderable
     *
     * Will be mapped to translatable entity
     * by TranslatableListener
     */
    protected $usageOrderable;

    public function __construct() {
        $this->count = 1;
        $this->datetime = new \DateTime('now', new \DateTimeZone('UTC'));
    }


    /**
     * Returns the id
     *
     * @return integer The ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets entity, that this translation should be mapped to.
     *
     * @param UsageOrderable $usageOrderable
     */
    public function setUsageOrderable($usaegOrderable)
    {
        $this->usageOrderable = $usaegOrderable;
    }

    /**
     * Returns entity, that this translation is mapped to.
     *
     * @return UsageOrderable
     */
    public function getUsageOrderable()
    {
        return $this->usageOrderable;
    }


    /**
     * @return \Datetime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }


    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param $count
     * @return $this
     */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }
}