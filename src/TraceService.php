<?php

namespace Pkerrigan\Xray;

use Pkerrigan\Xray\Submission\SegmentSubmitter;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * This layer sits ontop of the segment submitter to control which traces are submitted
 *
 * @author Niklas Ekman <nikl.ekman@gmail.com>
 * @since 01/07/2019
 */
class TraceService
{


    /**
     * @var SegmentSubmitter
     */
    private $segmentSubmitter;

    /**
     * @var Sampler
     */
    private $sampler;


    public function __construct(
        Sampler $sampler,
        SegmentSubmitter $segmentSubmitter
    ) {
        $this->segmentSubmitter = $segmentSubmitter;
        $this->sampler = $sampler;
    }

    /**
     * Adds a sampling decision to the Trace
     * @param Trace $trace
     * @param ServerRequestInterface $request
     * @return Trace
     * @throws InvalidArgumentException
     */
    public function addSamplingDecision(Trace $trace)
    {
        // Trace is already sampled.
        // Return true.
        if ($trace->isSampled()) {
            return $trace;
        }

        return $trace->setSampled($this->sampler->shouldSample($trace));
    }

    /**
     * Adds the sampling decision with a request object
     * @param Trace $trace
     * @param ServerRequestInterface $request
     * @return Trace
     * @throws InvalidArgumentException
     */
    public function addSamplingDecisionWithRequest(Trace $trace, ServerRequestInterface $request)
    {
        $uri = $request->getUri();
        $amazonHeader = $request->getHeaderLine('x-amzn-trace-id');

        $trace = $trace
            ->setTraceHeader($amazonHeader)
            ->setName($request->getUri()->getHost())
            ->setUrl(
                $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath() . $uri->getFragment()
            ) //Gets the scheme, host and path without params
            ->setMethod($request->getMethod())
            ->setUserAgent($request->getHeaderLine('user-agent'))
            ->setClientIpAddress($this->getClientAddress($request));

        $headerVariables = Utils::getHeaderParts($amazonHeader);
        if ($headerVariables !== null && isset($headerVariables['Sampled'])) {
            return $trace->setSampled(boolval($headerVariables['Sampled']));
        }

        return $this->addSamplingDecision($trace);
    }

    public function getClientAddress(ServerRequestInterface $request)
    {
        $forwardedFor = $request->getHeaderLine('x-forwarded-for');
        if (!empty($forwardedFor)) {
            return $forwardedFor;
        }

        return $request->getServerParams()['REMOTE_ADDR'];
    }


    /**
     * Submits a trace without deciding the sampling
     * @param Trace $trace
     */
    public function submitTrace(Trace $trace)
    {
        if ($trace->isSampled()) {
            $this->segmentSubmitter->submitSegment($trace);
        }
    }
}
