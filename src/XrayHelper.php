<?php

namespace Pkerrigan\Xray;

use Aws\XRay\XRayClient;
use Psr\SimpleCache\CacheInterface;
use Pkerrigan\Xray\Sampling\RuleRepository\AwsSdkRuleRepository;
use Pkerrigan\Xray\Sampling\TargetRepository\AwsSdkTargetRepository;
use Pkerrigan\Xray\Sampling\RuleRepository\CachedRuleRepository;
use Pkerrigan\Xray\Sampling\StateManager;
use Pkerrigan\Xray\Sampling\SamplerCache;
use Pkerrigan\Xray\Segment\Segment;
use Pkerrigan\Xray\Sampler;
use Pkerrigan\Xray\Trace;

/**
 *
 * @author Shueh Chou Lu <evan.lu@104.com.tw>
 * @since 2020/12/2
 * @internal
 */
class XrayHelper
{
    /**
     * Get Sampler in short line
     *
     * @param XRayClient $xrayClient
     * @param CacheInterface $psrCacher
     * @param array $fallbackSamplingRule (optional)
     * @return Sampler
     */
    public static function buildSampler(
        XRayClient $xrayClient,
        CacheInterface $psrCacher,
        array $fallbackSamplingRule = null
    ) {
        $samplingRuleRepository = new AwsSdkRuleRepository(
            $xrayClient,
            $fallbackSamplingRule
        );
        $samplingTargetRepository = new AwsSdkTargetRepository($xrayClient);
        // cache the rules every hour
        $cachedSamplingRuleRepository = new CachedRuleRepository(
            $samplingRuleRepository,
            $psrCacher
        );
        // state from different requests
        $stateManager = new StateManager($psrCacher);
        // cache service for sampling
        $samplerCache = new SamplerCache(
            $cachedSamplingRuleRepository,
            $samplingTargetRepository,
            $stateManager
        );

        return new Sampler($samplerCache);
    }

    /**
     * Run callable function with subsegment for X-Ray
     * @param callable $callback
     * @param Segment  $subsegment
     * @param array    $argments (optional)
     * @return mixed
     */
    public static function runWithSubsegment(
        callable $callback,
        Segment $subsegment,
        array $arguments = []
    ) {
        if (!Trace::getInstance()->isSampled()) {
            return call_user_func_array($callback, $arguments);
        }

        Trace::getInstance()
            ->getCurrentSegment()
            ->addSubsegment($subsegment);

        $result = call_user_func_array($callback, $arguments);

        $subsegment->end();

        return $result;
    }
}
