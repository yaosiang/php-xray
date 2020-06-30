<?php

namespace Pkerrigan\Xray\Sampling;

use Pkerrigan\Xray\Trace;
use Pkerrigan\Xray\Utils;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Manages state. In most other multi-threaded applications this is the runtime process.
 *
 * Class StateManager
 * @package Pkerrigan\Xray\Sampling
 */
class StateManager
{
    const CLIENT_ID_CACHE = 'StateManagerClientID';
    const LAST_TARGET_UPDATE = 'StateManagerLastTargetUpdate';

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Gets the timestamp of the last target update
     * @return int
     * @throws InvalidArgumentException
     */
    public function getLastTargetUpdate()
    {
        $segment = Trace::getInstance()->startSubsegment('StateManager::getLastTargetUpdate');

        $rule = null;
        if ($this->cache->has(self::LAST_TARGET_UPDATE)) {
            $targetUpdate = $this->cache->get(self::LAST_TARGET_UPDATE);
            $segment->end();
            return $targetUpdate;
        }
        $segment->end();
        return 0;
    }

    /**
     * Sets the timestamp of the last target update
     * @param $lastTargetUpdate
     * @return void
     * @throws CacheError
     * @throws InvalidArgumentException
     */
    public function setLastTargetUpdate($lastTargetUpdate)
    {
        $segment = Trace::getInstance()->startSubsegment('StateManager::setLastTargetUpdate');

        $rule = null;
        if (!$this->cache->set(self::LAST_TARGET_UPDATE, $lastTargetUpdate)) {
            throw new CacheError("Failed to save last target update");
        }
        $segment->end();
    }


    /**
     * Gets a merged rule for a rule
     * @param Rule $rule
     * @return Rule
     * @throws InvalidArgumentException
     */
    public function updateRule(Rule $rule)
    {
        $segment = Trace::getInstance()->startSubsegment('StateManager::updateRule');
        if (($oldRule = $this->getRule($rule->getCacheKey())) !== null) {
            $rule = $rule->merge($oldRule);
        }

        $this->saveRule($rule);
        $segment->end();
        return $rule;
    }

    /**
     * Gets a rule from a cache key
     * @param string $cacheKey
     * @return Rule
     * @throws InvalidArgumentException
     */
    public function getRule($cacheKey)
    {
        $segment = Trace::getInstance()->startSubsegment('StateManager::getRule');
        $cacheKey = Utils::stripInvalidCharacters($cacheKey);

        $rule = null;
        if ($this->cache->has($cacheKey)) {
            $rule = $this->cache->get($cacheKey);
        }
        $segment->end();
        return $rule;
    }

    /**
     * Saves a Rule
     * @param Rule $rule
     * @throws InvalidArgumentException
     * @throws CacheError
     */
    public function saveRule(Rule $rule)
    {
        $segment = Trace::getInstance()->startSubsegment('StateManager::saveRule');
        if (!$this->cache->set($rule->getCacheKey(), $rule)) {
            throw new CacheError("Failed to save rule " . $rule->getName());
        }
        $segment->end();
    }

    /**
     * @param Rule[] $rules
     */
    public function getAllSavedRulesFromRules(array $rules)
    {
        $segment = Trace::getInstance()->startSubsegment('StateManager::getAllSavedRulesFromRules');
        $savedRules = [];
        foreach ($rules as $rule) {
            $savedRules[] = $this->updateRule($rule);
        }
        $segment->end();
        return $savedRules;
    }


    /**
     * Gets a cached client id for this app process
     * @return string
     * @throws CacheError
     * @throws InvalidArgumentException
     */
    public function getClientInstanceId()
    {
        $segment = Trace::getInstance()->startSubsegment('StateManager::getClientInstanceId');

        if ($this->cache->has(self::CLIENT_ID_CACHE)) {
            $clientID = $this->cache->get(self::CLIENT_ID_CACHE);
            $segment->end();
            return $clientID;
        }

        $clientID = bin2hex(random_bytes(12));
        if (!$this->cache->set(self::CLIENT_ID_CACHE, $clientID)) {
            throw new CacheError("Failed to save client id ${$clientID}");
        }
        $segment->end();
        return $clientID;
    }
}
