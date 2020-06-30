<?php

namespace Pkerrigan\Xray;

use Pkerrigan\Xray\Sampling\Rule;
use Pkerrigan\Xray\Sampling\RuleMatcher;
use Pkerrigan\Xray\Sampling\SamplerCache;
use Pkerrigan\Xray\Segment\Segment;
use Psr\SimpleCache\InvalidArgumentException;

class Sampler
{
    /**
     * @var SamplerCache
     */
    private $samplerCache;

    public function __construct(SamplerCache $samplerCache)
    {
        $this->samplerCache = $samplerCache;
    }

    /**
     * @param Trace $trace
     * @return bool|string
     * @throws InvalidArgumentException
     */
    public function shouldSample(Trace $trace)
    {
        $segment = $trace->startSubsegment('Sampler::shouldSample');

        $now = time();
        // Match the trace against a rule.
        $matchedRule = $this->getMatchedRule($trace);

        if ($matchedRule !== null) {
            $trace->setMatchedRule($matchedRule);
            $response = $this->processMatchedRule($matchedRule, $now, $trace);
            $segment->end();
            return $response;
        } else {
            // TODO: Add a local sampler if we care, general consensus is if we can't load rules, dont sample
            //'No effective centralized sampling rule match. Fallback to local rules.'
            //return this.localSampler.shouldSample(sampleRequest);
            $segment->end();
            return false;
        }
    }

    /**
     *
     * @param Trace $trace
     * @return Rule
     * @throws InvalidArgumentException
     */
    public function getMatchedRule(Trace $trace)
    {
        $segment = $trace->startSubsegment('Sampler::getMatchedRule');

        if (($rule = RuleMatcher::matchFirst($trace, $this->samplerCache->getAllRules())) !== null) {
            $segment->end();
            return $rule;
        }

        $segment->end();
        return null;
    }


    /**
     * Taken heavily from
     * https://github.com/aws/aws-xray-sdk-node/blob/master/packages/core/lib/middleware/sampling/default_sampler.js#L65-L86
     *
     * Processes the rule we matched against and does the following:
     *  1. Increment counters on the rule depending on our quota
     *  2. Save the state back to the process cache
     *  3. Return if we should sample based on the quotas
     *
     * @param Rule $rule
     * @param int $now (unix timestamp in sec)
     * @param Trace $trace
     * @return bool|string
     * @throws InvalidArgumentException
     */
    private function processMatchedRule(Rule $rule, $now, Trace $trace)
    {
        $segment = $trace->startSubsegment('Sampler::processMatchedRule');

        //As long as a rule is matched we increment request counter.
        $rule->incrementRequestCount();

        $reservoir = $rule->getReservoir();

        $sample = true;

        // We check if we can borrow or take from reservoir first.
        $decision = $reservoir->borrowOrTake($now, $rule->canBorrow());
        if ($decision === 'borrow') {
            $rule->incrementBorrowedCount();
        } elseif ($decision === 'take') {
            $rule->incrementSampledCount();
            // Otherwise we compute based on FixedRate of this sampling rule.
        } elseif (Utils::randomPossibility($rule->getFixedRate() * 100)) {
            $rule->incrementSampledCount();
        } else {
            $sample = false;
        }

        $this->samplerCache->saveRule($rule);

        $segment->end();
        return $sample;
    }
}
