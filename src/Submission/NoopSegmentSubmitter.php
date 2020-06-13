<?php

namespace Pkerrigan\Xray\Submission;

use Pkerrigan\Xray\Segment\Segment;
use Psr\Log\LoggerInterface;

/**
 *
 */
class NoopSegmentSubmitter implements SegmentSubmitter
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Segment $segment
     * @return void
     */
    public function submitSegment(Segment $segment)
    {
        $this->logger->debug("Using Noop X-Ray Submmiter. Not submitting segment", [
            'name' => $segment->getName(),
            'id' => $segment->getId()
        ]);
    }
}
