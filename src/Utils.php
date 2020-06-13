<?php

namespace Pkerrigan\Xray;

use Pkerrigan\Xray\Sampling\Rule;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 30/06/2019
 * @internal
 */
class Utils
{
    /**
     * Sorts rules by priority.
     * If priority is the same we sort name by alphabet as rule name is unique.
     * https://github.com/aws/aws-xray-sdk-node/blob/master/packages/core/lib/middleware/sampling/rule_cache.js#L50-L52
     *
     * @param Rule[] $samplingRules
     * @return Rule[]
     */
    public static function sortSamplingRulesByPriorityDescending($samplingRules)
    {
        usort($samplingRules, function (Rule $samplingRule, Rule $samplingRuleOther) {
            $priority = $samplingRule->getPriority() - $samplingRuleOther->getPriority();
            if ($priority !== 0) {
                return $priority;
            }

            if ($samplingRule->getName() > $samplingRuleOther->getName()) {
                return 1;
            } else {
                return -1;
            }
        });

        return $samplingRules;
    }

    /**
     * @param int $percentage
     * @return bool
     */
    public static function randomPossibility($percentage)
    {
        return random_int(0, 99) < $percentage;
    }
}
