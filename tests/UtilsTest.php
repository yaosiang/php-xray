<?php

namespace Pkerrigan\Xray;

use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\Sampling\Rule;

class UtilsTest extends TestCase
{
    /**
     *
     * @dataProvider provideSortSamplingRulesByPriority
     */
    public function testSortSamplingRulesByPriority($samplingRules, $expected)
    {
        $this->assertEquals($expected, Utils::sortSamplingRulesByPriorityDescending($samplingRules));
    }

    public function provideSortSamplingRulesByPriority()
    {
        return [
            'Sort by priority descending' => [
                [
                    (new Rule())->populateFromAWS([
                        'Priority' => 1000,
                        'RuleName' => 'Default'
                    ]),
                    (new Rule())->populateFromAWS([
                        'Priority' => 1,
                        'RuleName' => 'Important'
                    ])
                ],
                [
                    (new Rule())->populateFromAWS([
                        'Priority' => 1,
                        'RuleName' => 'Important'
                    ]),
                    (new Rule())->populateFromAWS([
                        'Priority' => 1000,
                        'RuleName' => 'Default'
                    ])
                ]
            ]
        ];
    }
}
