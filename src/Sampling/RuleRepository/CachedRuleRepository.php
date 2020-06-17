<?php

namespace Pkerrigan\Xray\Sampling\RuleRepository;

use Pkerrigan\Xray\Sampling\CacheError;
use Pkerrigan\Xray\Sampling\Rule;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Proxy class used to cache retrieval of sampling rules
 *
 * @author Niklas Ekman <nikl.ekman@gmail.com>
 * @since 30/06/2019
 */
class CachedRuleRepository implements RuleRepository
{

    const CACHE_KEY = 'PkerriganXraySamplingRules';

    /** @var RuleRepository */
    private $samplingRuleRepository;

    /** @var CacheInterface */
    private $cache;

    /** @var int */
    private $cacheTtlSeconds;

    public function __construct(
        RuleRepository $samplingRuleRepository,
        CacheInterface $cache,
        $cacheTtlSeconds = 3600
    ) {
        $this->samplingRuleRepository = $samplingRuleRepository;
        $this->cache = $cache;
        $this->cacheTtlSeconds = $cacheTtlSeconds;
    }

    /**
     * @return Rule[]
     * @throws CacheError
     * @throws InvalidArgumentException
     */
    public function getAll()
    {
        if ($this->cache->has(self::CACHE_KEY)) {
            return $this->cache->get(self::CACHE_KEY);
        }

        $samplingRules = $this->samplingRuleRepository->getAll();
        if (!$this->cache->set(self::CACHE_KEY, $samplingRules, $this->cacheTtlSeconds)) {
            throw new CacheError('Failed to save sampling rules to the cache');
        }

        return $samplingRules;
    }
}
