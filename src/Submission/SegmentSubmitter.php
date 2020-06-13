<?php

namespace Pkerrigan\Xray\Submission;

use Pkerrigan\Xray\Segment;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 13/05/2018
 */
interface SegmentSubmitter
{
    /**
     * @param Segment $segment
     */
    public function submitSegment(Segment $segment);
}
