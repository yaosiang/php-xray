<?php

namespace Pkerrigan\Xray;

use PHPUnit\Framework\TestCase;
use Aws\XRay\XRayClient;
use Pkerrigan\Xray\XrayHelper;
use Psr\SimpleCache\CacheInterface;
use Pkerrigan\Xray\Segment\Segment;
use Pkerrigan\Xray\Segment\HttpSegment;
use Pkerrigan\Xray\Sampler;
use Pkerrigan\Xray\Trace;

/**
 *
 * @author Shueh Chou Lu <evan.lu@104.com.tw>
 * @since 2020/12/2
 */
class XrayHelperTest extends TestCase
{
    public function testBuildSampler()
    {
        $testName = 'Testing1234';
        $testType = '123Type';

        $xrayClient = $this->createMock(XRayClient::class);

        $cache = $this->createMock(CacheInterface::class);

        $sampler = XrayHelper::buildSampler($xrayClient, $cache);
        $this->assertInstanceOf(Sampler::class, $sampler);
    }

    public function testRunWithSubsegmentWithSampled()
    {
        $trace = $this->createMock(Trace::class);
        $trace->expects($this->once())
            ->method('isSampled')
            ->willReturn(true);
        $trace->expects($this->once())
            ->method('addSubsegment')
            ->with($this->isInstanceOf(HttpSegment::class));
        $trace->expects($this->once())
            ->method('getCurrentSegment')
            ->willReturn($trace);
        Trace::setInstance($trace);

        $callback = function () {
            return 'success';
        };

        $subsegment = $this->createMock(HttpSegment::class);
        $subsegment->expects($this->once())
            ->method('end');

        $result = XrayHelper::runWithSubsegment($callback, $subsegment);

        $this->assertSame($result, 'success');
    }

    public function testRunWithSubsegmentWithoutSampled()
    {
        $trace = $this->createMock(Trace::class);
        $trace->expects($this->once())
            ->method('isSampled')
            ->willReturn(false);
        Trace::setInstance($trace);

        $callback = function () {
            sleep(1);
            return 'success';
        };

        $subsegment = $this->createMock(HttpSegment::class);
        $subsegment->expects($this->never())
            ->method('end');

        $result = XrayHelper::runWithSubsegment($callback, $subsegment);

        $this->assertSame($result, 'success');
    }
}
