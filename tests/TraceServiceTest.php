<?php

namespace Pkerrigan\Xray;

use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\Sampling\Rule;
use Pkerrigan\Xray\Sampling\RuleRepository\RuleRepository;
use Pkerrigan\Xray\Submission\SegmentSubmitter;

class TraceServiceTest extends TestCase
{
    public function testSubmitTrace()
    {
        $samplingRuleRepo = $this->createMock(RuleRepository::class);
        $samplingRuleRepo->expects($this->once())
            ->method('getAll')
            ->willReturn([(new Rule())->setFixedRate(0.25)]);

        $segmentSubmitter = $this->createMock(SegmentSubmitter::class);
        $segmentSubmitter->expects($this->atMost(1))
            ->method('submitSegment');

        $traceService = new TraceService($samplingRuleRepo, $segmentSubmitter);

        $traceService->submitTrace(new Trace());
    }
}
