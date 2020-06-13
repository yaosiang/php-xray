<?php

namespace Pkerrigan\Xray\Sampling\RuleRepository;

use PHPUnit\Framework\TestCase;
use Aws\XRay\XRayClient;
use Aws\Exception\AwsException;
use Pkerrigan\Xray\Sampling\Rule;

class AwsSdkSamplingRuleRepositoryTest extends TestCase
{

    public function testGetAll()
    {
        $testName = 'Testing1234';
        $testType = '123Type';

        $xrayClient = $this->createMock(XRayClient::class);
        $xrayClient->expects($this->once())
            ->method('getPaginator')
            ->with($this->equalTo('GetSamplingRules'))
            ->willReturn(new \ArrayIterator([
                [
                    'SamplingRuleRecords' => [
                        [
                            'SamplingRule' => [
                                'ServiceName' => $testName,
                                'ServiceType' => $testType
                            ]
                        ]
                    ]
                ]
            ]));

        $repository = new AwsSdkRuleRepository($xrayClient);

        $expected = [
            (new Rule())->setServiceName($testName)->setServiceType($testType)
        ];

        $this->assertEquals($expected, $repository->getAll());
    }

    public function testGetAllAwsErrorWithFallback()
    {
        $testName = 'Testing1234';

        $exception = $this->createMock(AwsException::class);

        $xrayClient = $this->createMock(XRayClient::class);
        $xrayClient->expects($this->once())
            ->method('getPaginator')
            ->with($this->equalTo('GetSamplingRules'))
            ->will($this->throwException($exception));

        $fallbackSamplingRule = ['ServiceName' => $testName];

        $repository = new AwsSdkRuleRepository($xrayClient, $fallbackSamplingRule);

        $samplingRules = $repository->getAll();

        $this->assertCount(1, $samplingRules);
        $this->assertEquals((new Rule())->setServiceName($testName)->toAWS(), $samplingRules[0]->toAWS());
    }

    public function testGetAllAwsErrorWithoutFallback()
    {
        $exception = $this->createMock(AwsException::class);

        $this->expectException(get_class($exception));

        $xrayClient = $this->createMock(XRayClient::class);
        $xrayClient->expects($this->once())
            ->method('getPaginator')
            ->with($this->equalTo('GetSamplingRules'))
            ->will($this->throwException($exception));

        $repository = new AwsSdkRuleRepository($xrayClient);

        $repository->getAll();
    }
}
