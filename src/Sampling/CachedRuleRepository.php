<?php

namespace Pkerrigan\Xray\Sampling;

use Psr\SimpleCache\CacheInterface;

/**
 * Proxy class used to cache retrieval of sampling rules
 *
 * @author Niklas Ekman <nikl.ekman@gmail.com>
 * @since 30/06/2019
 */
class CachedRuleRepository implements RuleRepository
{

    const CACHE_KEY = 'Pkerrigan\\Xray\\Sampling\Rules';

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
     */
    public function getAll()
    {
        if ($this->cache->has(self::CACHE_KEY)) {
            return $this->cache->get(self::CACHE_KEY);
        }

        $samplingRules = $this->samplingRuleRepository->getAll();
        $this->cache->set(self::CACHE_KEY, $samplingRules, $this->cacheTtlSeconds);

        return $samplingRules;
    }
}
