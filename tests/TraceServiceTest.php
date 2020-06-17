<?php

namespace Pkerrigan\Xray;

use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\Submission\SegmentSubmitter;

class TraceServiceTest extends TestCase
{
    public function testSubmitTrace()
    {
        $sampler = $this->createMock(Sampler::class);
        $sampler->expects($this->once())
            ->method('shouldSample')
            ->willReturn(true);

        $segmentSubmitter = $this->createMock(SegmentSubmitter::class);
        $segmentSubmitter->expects($this->atMost(1))
            ->method('submitSegment');

        $traceService = new TraceService($sampler, $segmentSubmitter);

        $trace = $traceService->addSamplingDecision(new Trace());
        $traceService->submitTrace($trace);
    }

    public function testSubmitTraceNoSampling()
    {
        $sampler = $this->createMock(Sampler::class);
        $sampler->expects($this->once())
            ->method('shouldSample')
            ->willReturn(false);

        $segmentSubmitter = $this->createMock(SegmentSubmitter::class);
        $segmentSubmitter->expects($this->atMost(0))
            ->method('submitSegment');

        $traceService = new TraceService($sampler, $segmentSubmitter);

        $trace = $traceService->addSamplingDecision(new Trace());
        $traceService->submitTrace($trace);
    }
}
