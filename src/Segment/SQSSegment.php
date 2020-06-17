<?php

namespace Pkerrigan\Xray\Segment;

/**
 * Class SQSSegment
 * @package Pkerrigan\Xray\Segment
 */
class SQSSegment extends AWSSegment
{

    /**
     * For operations on an Amazon SQS queue, the queue's URL.
     * @var string $queueUrl
     */
    private $queueUrl;

    /**
     * @return string
     */
    public function getQueueUrl()
    {
        return $this->queueUrl;
    }

    /**
     * @param string $queueUrl
     * @return SQSSegment
     */
    public function setQueueUrl($queueUrl)
    {
        $this->queueUrl = $queueUrl;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['aws']['queue_url'] = $this->getQueueUrl();

        return array_filter($data);
    }
}
