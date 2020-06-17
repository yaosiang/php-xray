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

    /**
     * This strips out invalid characters from memcached keys
     *
     * @param $key
     * @return string
     */
    public static function stripInvalidCharacters($key)
    {
        return preg_replace("/[\{\}\(\)\/\\\@:]/", '', $key);
    }

    public static function getHeaderParts($traceHeader)
    {
        if (is_null($traceHeader)) {
            return null;
        }

        $parts = explode(';', $traceHeader);

        $variables = array_map(function ($str) {
            return explode('=', $str);
        }, $parts);

        $variables = array_column($variables, 1, 0);
        return $variables;
    }
}
