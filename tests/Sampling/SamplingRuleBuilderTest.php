<?php

namespace Pkerrigan\Xray\Sampling;

use PHPUnit\Framework\TestCase;

class SamplingRuleBuilderTest extends TestCase
{

    public function testBuild()
    {
        $samplingRule = (new Rule())
            ->setFixedRate(0.75)
            ->setHost('example.com')
            ->setServiceName('app.example.com')
            ->setServiceType('*')
            ->setUrlPath('/my/path');

        $expected = [
            'FixedRate' => 0.75,
            'HTTPMethod' => '*',
            'Host' => 'example.com',
            'Priority' => 1,
            'ReservoirSize' => 1,
            'ResourceARN' => '*',
            'RuleARN' => '*',
            'RuleName' => 'Pkerrigan\\Xray\\Sampling\\Rule',
            'ServiceName' => 'app.example.com',
            'ServiceType' => '*',
            'URLPath' => '/my/path'
        ];

        $this->assertEquals($expected, $samplingRule->toAWS());
    }

    public function testBuildWithCopyConstructor()
    {
        $copySamplingRule = (new Rule())
            ->setHost('example.com')
            ->setServiceName('app.example.com')
            ->toAWS();

        $samplingRuleBuilder = (new Rule())
            ->populateFromAWS($copySamplingRule)
            ->setUrlPath('/path');

        $expected = [
            'FixedRate' => 1.0,
            'HTTPMethod' => '*',
            'Host' => 'example.com',
            'Priority' => 1,
            'ReservoirSize' => 1,
            'ResourceARN' => '*',
            'RuleARN' => '*',
            'RuleName' => 'Pkerrigan\\Xray\\Sampling\\Rule',
            'ServiceName' => 'app.example.com',
            'ServiceType' => '*',
            'URLPath' => '/path'
        ];

        $this->assertEquals($expected, $samplingRuleBuilder->toAWS());
    }
}
