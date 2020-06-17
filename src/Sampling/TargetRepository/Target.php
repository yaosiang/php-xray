<?php

namespace Pkerrigan\Xray\Sampling\TargetRepository;

/**
 * Class Target
 * @package Pkerrigan\Xray\Sampling\TargetRepository
 */
class Target
{
    /**
     * @var float
     */
    private $rate;

    /**
     * @var int
     */
    private $quota;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var string
     */
    private $ruleName;

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     * @return static
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuota()
    {
        return $this->quota;
    }

    /**
     * @param int $quota
     * @return static
     */
    public function setQuota($quota)
    {
        $this->quota = $quota;
        return $this;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     * @return static
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param int $interval
     * @return static
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @return string
     */
    public function getRuleName()
    {
        return $this->ruleName;
    }

    /**
     * @param string $ruleName
     * @return static
     */
    public function setRuleName($ruleName)
    {
        $this->ruleName = $ruleName;
        return $this;
    }
}
