<?php
namespace Pkerrigan\Xray;

use Pkerrigan\Xray\SamplingRule\SamplingRuleMatcher;
use Pkerrigan\Xray\SamplingRule\SamplingRuleRepository;
use Pkerrigan\Xray\Submission\DaemonSegmentSubmitter;
use Pkerrigan\Xray\Submission\SegmentSubmitter;

/**
 * This layer sits ontop of the segment submitter to control which traces are submitted
 * 
 * @author Niklas Ekman <nikl.ekman@gmail.com>
 * @since 01/07/2019
 */
class TraceService
{

    /** @var SamplingRuleRepository */
    private $samplingRuleRepository;
    
    /** @var SegmentSubmitter */
    private $segmentSubmitter;


    public function __construct(
        SamplingRuleRepository $samplingRuleRepository, 
        SegmentSubmitter $segmentSubmitter = null
    )
    {
        $this->samplingRuleRepository = $samplingRuleRepository;
        $this->segmentSubmitter = ($segmentSubmitter !== null) ? $segmentSubmitter : new DaemonSegmentSubmitter();
    }

    public function submitTrace(Trace $trace)
    {
        $samplingRules = $this->samplingRuleRepository->getAll();
        $samplingRule = SamplingRuleMatcher::matchFirst($trace, $samplingRules);
        
        $isSampled = $samplingRule !== null && Utils::randomPossibility($samplingRule['FixedRate'] * 100);
        $trace->setSampled($isSampled);

        if ($isSampled) {
            $this->segmentSubmitter->submitSegment($trace);
        }
    }
}

