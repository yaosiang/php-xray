<?php

namespace Pkerrigan\Xray\Sampling;

use Pkerrigan\Xray\Sampling\RuleRepository\RuleRepository;
use Pkerrigan\Xray\Sampling\TargetRepository\AwsSdkTargetRepository;
use Pkerrigan\Xray\Sampling\TargetRepository\Target;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class SamplerCache
 * @package Pkerrigan\Xray
 */
class SamplerCache
{

    /**
     * @var RuleRepository
     */
    private $ruleRepository;
    /**
     * @var AwsSdkTargetRepository
     */
    private $awsSdkTargetRepository;
    /**
     * @var StateManager
     */
    private $stateManager;

    public function __construct(
        RuleRepository $ruleRepository,
        AwsSdkTargetRepository $awsSdkTargetRepository,
        StateManager $stateManager
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->awsSdkTargetRepository = $awsSdkTargetRepository;
        $this->stateManager = $stateManager;
    }


    /**
     * @return Rule[]
     * @throws InvalidArgumentException
     */
    public function getAllRules()
    {
        return $this->stateManager->getAllSavedRulesFromRules($this->ruleRepository->getAll());
    }


    /**
     * Saves the rule to the cache
     *
     * @param Rule $rule
     * @throws InvalidArgumentException
     */
    public function saveRule(Rule $rule)
    {
        $this->stateManager->saveRule($rule);
        $this->refreshTargets();
    }



    /**************************
     * Target Sample Updates
     *************************/

    /**
     * @throws InvalidArgumentException
     */
    private function refreshTargets()
    {
        //TODO: see if we should report data (10 sec)
        $candidates = $this->getCandidates();
        if (!count($candidates)) {
            //No data to report
            return;
        }

        $targets = $this->awsSdkTargetRepository->getAll($candidates, $this->stateManager->getClientInstanceId());

        foreach ($targets as $target) {
            $this->updateRuleQuota($target);
        }
    }


    // Don't report a rule statistics if any of the conditions is met:
    // 1. The report time hasn't come (some rules might have larger report intervals).
    // 2. The rule is never matched.
    private function getCandidates()
    {
        $rules = $this->getAllRules();

        $candidates = [];
        foreach ($rules as $rule) {
            if ($rule->everMatched() && $rule->timeToReport()) {
                $candidates[] = $rule;
            }
        }

        return $candidates;
    }


    /**
     * @param Target $target
     * @throws InvalidArgumentException
     */
    private function updateRuleQuota(Target $target)
    {
        $rule = $this->stateManager->getRule($target->getRuleName());

        if ($rule === null) {
            //No rule to update
            return;
        }

        $rule->resetStatistics();
        $rule->getReservoir()->loadNewQuota(
            $target->getQuota(),
            $target->getTtl(),
            $target->getInterval()
        );

        $rule->setFixedRate($target->getRate());
        $this->stateManager->saveRule($rule);
    }
}
