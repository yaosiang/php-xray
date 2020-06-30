<?php

namespace Pkerrigan\Xray\Sampling;

use Pkerrigan\Xray\Segment\Segment;
use Pkerrigan\Xray\Trace;
use Pkerrigan\Xray\Utils;

/**
 *
 * @author Niklas Ekman <nikl.ekman@gmail.com>
 * @since 01/07/2019
 * @see https://docs.aws.amazon.com/xray/latest/devguide/xray-console-sampling.html
 */
class RuleMatcher
{
    /**
     * @param Trace $trace
     * @param Rule[] $samplingRules
     * @return Rule|null
     */
    public static function matchFirst(Trace $trace, array $samplingRules)
    {
        $segment = (new Segment())
            ->begin()
            ->setName('RuleMatcher::matchFirst');
        $trace->addSubsegment($segment);

        $samplingRules = Utils::sortSamplingRulesByPriorityDescending($samplingRules);

        foreach ($samplingRules as $samplingRule) {
            if (self::match($trace, $samplingRule)) {
                $segment->end();
                return $samplingRule;
            }
        }

        $segment->end();
        return null;
    }

    /**
     * @param Trace $trace
     * @param Rule $samplingRule
     * @return bool
     */
    public static function match(Trace $trace, $samplingRule)
    {
        $url = parse_url($trace->getUrl());

        $criterias = [
            $samplingRule->getServiceName() => $trace->getName() ? $trace->getName() : '',
            $samplingRule->getServiceType() => $trace->getType() ? $trace->getType() : '',
            $samplingRule->getHttpMethod() => $trace->getMethod() ? $trace->getMethod() : '',
            $samplingRule->getUrlPath() => isset($url['path']) ? $url['path'] : '',
            $samplingRule->getHost() => isset($url['host']) ? $url['host'] : ''
        ];

        foreach ($criterias as $criteria => $input) {
            if (!self::stringMatchesCriteria($input, $criteria)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $input
     * @param string $criteria
     * @return bool
     */
    public static function stringMatchesCriteria($input, $criteria)
    {
        /*
         * Check if a criteria matches a given input. A criteria can include a multi-character wildcard (*)
         * or a single-character wildcard (?)
         * See: https://docs.aws.amazon.com/xray/latest/devguide/xray-console-sampling.html?icmpid=docs_xray_console#xray-console-sampling-options
         */
        // Lets use regex in order to determine if the criteria matches. Quoting the criteria
        // will assure that the user can't enter any arbitray regex in the AWS console
        $criteria = str_replace(['\\*', '\\?'], ['.*', '.{1}'], preg_quote($criteria, '/'));

        return preg_match("/^{$criteria}$/i", $input) === 1;
    }
}
