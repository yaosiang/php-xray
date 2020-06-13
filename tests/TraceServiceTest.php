<?php

namespace Pkerrigan\Xray;

use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\SamplingRule\SamplingRuleBuilder;
use Pkerrigan\Xray\SamplingRule\SamplingRuleRepository;
use Pkerrigan\Xray\Submission\SegmentSubmitter;

class TraceServiceTest extends TestCase
{
    public function testSubmitTrace()
    {
        $samplingRuleRepo = $this->createMock(SamplingRuleRepository::class);
        $samplingRuleRepo->expects($this->once())
            ->method('getAll')
            ->willReturn([(new SamplingRuleBuilder(['FixedRate' => 0.25]))->build()]);

        $segmentSubmitter = $this->createMock(SegmentSubmitter::class);
        $segmentSubmitter->expects($this->atMost(1))
            ->method('submitSegment');

        $traceService = new TraceService($samplingRuleRepo, $segmentSubmitter);

        $traceService->submitTrace(new Trace());
    }
}

