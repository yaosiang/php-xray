<?php

namespace Pkerrigan\Xray\Submission;

use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\Segment\Segment;
use Psr\Log\Test\TestLogger;
use Symfony\Bridge\PhpUnit\SetUpTearDownTrait;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 17/05/2018
 */
class NoopSegmentSubmitterTest extends TestCase
{
    use SetUpTearDownTrait;

    /**
     * @var TestLogger
     */
    private $logger;

    /**
     * @var SegmentSubmitter
     */
    private $subject;


    public function doSetup()
    {
        $this->logger = new TestLogger();
        $this->subject = new NoopSegmentSubmitter($this->logger);
    }

    public function testSubmitsToDaemon()
    {
        $segment = new Segment();
        $segment->setSampled(true)
            ->setName('Test segment')
            ->begin()
            ->end()
            ->submit($this->subject);

        $this->assertSegmentReceived($segment);
    }

    /**
     * @param Segment $segment
     */
    private function assertSegmentReceived(Segment $segment)
    {
        $this->logger->hasDebug([
            'message' => 'Using Noop X-Ray Submmiter. Not submitting segment',
            'context' => [
                'name' => $segment->getName(),
                'id' => $segment->getId()
            ]
        ]);
    }
}
