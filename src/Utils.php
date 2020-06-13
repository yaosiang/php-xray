<?php

namespace Pkerrigan\Xray;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 30/06/2019
 * @internal
 */
class Utils
{
    /**
     * @param array $samplingRules
     * @return array
     */
    public static function sortSamplingRulesByPriorityDescending($samplingRules)
    {
        usort($samplingRules, function ($samplingRule, $samplingRuleOther) {
            return $samplingRule['Priority'] - $samplingRuleOther['Priority'];
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

