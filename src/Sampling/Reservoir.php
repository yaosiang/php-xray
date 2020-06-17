<?php

namespace Pkerrigan\Xray\Sampling;

/**
 * https://github.com/aws/aws-xray-sdk-node/blob/master/packages/core/lib/middleware/sampling/reservoir.js
 *
 * Represents a Reservoir object that keeps track of the number of traces per second sampled and
 * the fixed rate for a given sampling rule. This information is fetched from X-Ray serivce.
 * It decides if a given trace should be borrowed or sampled or not sampled based on the state of current second.
 */
class Reservoir
{
    /**
     * @var null|int
     */
    private $quota = null;

    /**
     * Time to live in unix time (secs)
     * @var null|int
     */
    private $TTL = null;

    /**
     * Number of samples taken
     * @var int
     */
    private $takenThisSec = 0;

    /**
     * Number of samples borrowed
     * @var int
     */
    private $borrowedThisSec = 0;

    /**
     * Interval we should report at
     * @var int
     */
    private $reportInterval = 1;

    /**
     * @var int
     */
    private $reportElapsed = 0;

    /**
     * Represents the time in seconds that the current values represent.
     *
     * @var int (unix timestamp in sec)
     */
    private $thisSec = 0;

    /**
     * Should the sampler borrow or take from the reservoir.
     * And if so lets tell upstream what we did.
     *
     * @param int $now seconds unix timestamp
     * @param $canBorrow
     * @return bool|string
     */
    public function borrowOrTake($now, $canBorrow)
    {
        //Reset the counters to the current seconds if we need to.
        $this->adjustThisSec($now);

        // Don't borrow if the quota is available and fresh.
        if ($this->quota >= 0 && $this->TTL >= $now) {
            if ($this->takenThisSec >= $this->quota) {
                return false;
            }

            $this->takenThisSec++;
            return 'take';
        }

        // Otherwise try to borrow if the quota is not present or expired.
        if ($canBorrow) {
            if ($this->borrowedThisSec >= 1) {
                return false;
            }

            $this->borrowedThisSec++;
            return 'borrow';
        }

        return false;
    }

    /**
     * If the seconds saved is not equal to now, lets reset the seconds counters
     *
     * @param $now
     */
    public function adjustThisSec($now)
    {
        if ($now !== $this->thisSec) {
            $this->takenThisSec = 0;
            $this->borrowedThisSec = 0;
            $this->thisSec = $now;
        }
    }

    /**
     * Load a new quota from AWS into this reservoir
     *
     * @param $quota
     * @param $TTL
     * @param $interval
     */
    public function loadNewQuota($quota, $TTL, $interval)
    {
        if ($quota) {
            $this->quota = $quota;
        }
        if ($TTL) {
            $this->TTL = $TTL;
        }
        if ($interval) {
            // Report interval is always time of 10.
            $this->reportInterval = $interval / 10;
        }
    }

    /**
     * Is it time to report?
     * @return bool
     */
    public function timeToReport()
    {
        if ($this->reportElapsed + 1 >= $this->reportInterval) {
            $this->reportElapsed = 0;
            return true;
        } else {
            $this->reportElapsed += 1;
            return false;
        }
    }
}
