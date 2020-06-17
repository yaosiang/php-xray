<?php

namespace Pkerrigan\Xray\Segment;

class AWSSegment extends HttpSegment
{

    /**
     * If your application accesses resources in a different account, or sends segments to a different account,
     * record the ID of the account that owns the AWS resource that your application accessed.
     * @var string $accountID
     */
    private $accountID;

    /**
     * The name of the API action invoked against an AWS service or resource.
     * @var string $operation
     */
    private $operation;

    /**
     * If the resource is in a region different from your application, record the region. For example, us-west-2.
     * @var string region
     */
    private $region;

    /**
     * Unique identifier for the request.
     * @var string $requestId
     */
    private $requestId;

    /**
     * @return string
     */
    public function getAccountID()
    {
        return $this->accountID;
    }

    /**
     * @param string $accountID
     * @return AWSSegment
     */
    public function setAccountID($accountID)
    {
        $this->accountID = $accountID;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     * @return AWSSegment
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string $region
     * @return AWSSegment
     */
    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     * @return AWSSegment
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data['namespace'] = 'aws';

        $aws = array_filter([
            'region' => $this->getRegion(),
            'operation' => $this->getOperation(),
            'account_id' => $this->getAccountID(),
            'request_id' => $this->getRequestId(),
        ]);

        if (isset($data['aws'])) {
            $data['aws'] = array_merge($data['aws'], $aws);
        } else {
            $data['aws'] = $aws;
        }

        return array_filter($data);
    }
}
