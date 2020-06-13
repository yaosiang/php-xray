<?php

namespace Pkerrigan\Xray\Submission;

use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\Segment\Segment;
use Pkerrigan\Xray\Segment\SqlSegment;
use Pkerrigan\Xray\Trace;
use Symfony\Bridge\PhpUnit\SetUpTearDownTrait;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 17/05/2018
 */
class DaemonSegmentSubmitterTest extends TestCase
{
    use SetUpTearDownTrait;

    /**
     * @var resource
     */
    private $socket;


    public function doSetup()
    {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_bind($this->socket, '127.0.0.1', 2000);
    }

    public function doTearDown()
    {
        socket_close($this->socket);
    }

    public function testSubmitsToDaemon()
    {
        $segment = new Segment();
        $segment->setSampled(true)
            ->setName('Test segment')
            ->begin()
            ->end()
            ->submit(new DaemonSegmentSubmitter());

        $packets = $this->receivePackets(1);

        $this->assertPacketsReceived([$segment], $packets);
    }

    public function testSubmitsLongTraceAsFragmented()
    {
        $subsegment1 = (new SqlSegment())
            ->setQuery(str_repeat('a', 30000));
        $subsegment2 = (new SqlSegment())
            ->setQuery(str_repeat('b', 30000));
        $subsegment3 = (new SqlSegment())
            ->setQuery(str_repeat('c', 30000));

        $segment = new Trace();
        $segment->setSampled(true)
                ->setName('Test segment')
                ->begin()
                ->addSubsegment($subsegment1)
                ->addSubsegment($subsegment2)
                ->addSubsegment($subsegment3)
                ->end()
                ->submit(new DaemonSegmentSubmitter());

        $buffer = $this->receivePackets(5);

        $rawSegment = $segment->jsonSerialize();
        unset($rawSegment['subsegments']);
        $openingSegment = $rawSegment;
        unset($openingSegment['end_time']);
        $openingSegment['in_progress'] = true;

        $subsegment1->setIndependent(true)
                    ->setTraceId($segment->getTraceId())
                    ->setParentId($segment->getId());

        $subsegment2->setIndependent(true)
                    ->setTraceId($segment->getTraceId())
                    ->setParentId($segment->getId());

        $subsegment3->setIndependent(true)
                    ->setTraceId($segment->getTraceId())
                    ->setParentId($segment->getId());

        $expectedPackets = [$openingSegment, $subsegment1, $subsegment2, $subsegment3, $rawSegment];

        $this->assertPacketsReceived($expectedPackets, $buffer);
    }

    /**
     * @param $expectedPackets
     * @param $buffer
     */
    private function assertPacketsReceived($expectedPackets, $buffer)
    {
        for ($i = 0; $i < count($expectedPackets); $i++) {
            $this->assertEquals(
                json_encode(DaemonSegmentSubmitter::HEADER) . "\n" . json_encode($expectedPackets[$i]),
                $buffer[$i]
            );
        }
    }

    /**
     * @param int $number
     * @return array
     */
    private function receivePackets($number)
    {
        $from = '';
        $port = 0;
        $buffer = array_fill(0, $number, '');

        for ($i = 0; $i < $number; $i++) {
            socket_recvfrom($this->socket, $buffer[$i], 65535, 0, $from, $port);
        }

        return $buffer;
    }
}
