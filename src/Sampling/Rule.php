<?php

namespace Pkerrigan\Xray\Sampling;

class Rule
{
    /**
     * The rate to sample 0-1
     *
     * @var float
     */
    private $fixedRate = 1.0;

    /**
     * The http method to sample
     * @var string
     */
    private $httpMethod = '*';

    /**
     * The host to sample
     * @var string
     */
    private $host = '*';

    /**
     * The priority of the rule
     * @var int
     */
    private $priority = 1;

    /**
     * @var int
     */
    private $reservoirSize = 1;

    /**
     * The resource ARN to sample
     * @var string
     */
    private $resourceARN = '*';

    /**
     * The rule arn to sample
     * @var string
     */
    private $ruleARN = '*';

    /**
     * The name of the rule
     * @var string
     */
    private $name = 'Pkerrigan\\Xray\\Sampling\\Rule';

    /**
     * The service name to sample
     * @var string
     */
    private $serviceName = '*';

    /**
     * The service type to sample
     * @var string
     */
    private $serviceType = '*';

    /**
     * The url path to sample
     * @var string
     */
    private $urlPath = '*';

    /**
     * @return float
     */
    public function getFixedRate()
    {
        return $this->fixedRate;
    }

    /**
     * @param float $fixedRate
     * @return Rule
     */
    public function setFixedRate($fixedRate)
    {
        $this->fixedRate = $fixedRate;
        return $this;
    }

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * @param string $httpMethod
     * @return Rule
     */
    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return Rule
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return Rule
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return int
     */
    public function getReservoirSize()
    {
        return $this->reservoirSize;
    }

    /**
     * @param int $reservoirSize
     * @return Rule
     */
    public function setReservoirSize($reservoirSize)
    {
        $this->reservoirSize = $reservoirSize;
        return $this;
    }

    /**
     * @return string
     */
    public function getResourceARN()
    {
        return $this->resourceARN;
    }

    /**
     * @param string $resourceARN
     * @return Rule
     */
    public function setResourceARN($resourceARN)
    {
        $this->resourceARN = $resourceARN;
        return $this;
    }

    /**
     * @return string
     */
    public function getRuleARN()
    {
        return $this->ruleARN;
    }

    /**
     * @param string $ruleARN
     * @return Rule
     */
    public function setRuleARN($ruleARN)
    {
        $this->ruleARN = $ruleARN;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Rule
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param string $serviceName
     * @return Rule
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * @param string $serviceType
     * @return Rule
     */
    public function setServiceType($serviceType)
    {
        $this->serviceType = $serviceType;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlPath()
    {
        return $this->urlPath;
    }

    /**
     * @param string $urlPath
     * @return Rule
     */
    public function setUrlPath($urlPath)
    {
        $this->urlPath = $urlPath;
        return $this;
    }

    /**
     * Returns the rule in the AWS Format
     * @return array
     */
    public function toAWS()
    {
        return [
            'FixedRate' => $this->getFixedRate(),
            'HTTPMethod' => $this->getHttpMethod(),
            'Host' => $this->getHost(),
            'Priority' => $this->getPriority(),
            'ReservoirSize' => $this->getReservoirSize(),
            'ResourceARN' => $this->getResourceARN(),
            'RuleARN' => $this->getRuleARN(),
            'RuleName' => $this->getName(),
            'ServiceName' => $this->getServiceName(),
            'ServiceType' => $this->getServiceType(),
            'URLPath' => $this->getUrlPath()
        ];
    }

    /**
     * Creates a Rule from AWS.
     * Not all fields are returned from AWS so we need to check if they are set.
     * @param array $sampleRule
     * @return Rule
     */
    public function populateFromAWS(array $sampleRule)
    {
        if (isset($sampleRule['FixedRate'])) {
            $this->setFixedRate($sampleRule['FixedRate']);
        }

        if (isset($sampleRule['HTTPMethod'])) {
            $this->setHttpMethod($sampleRule['HTTPMethod']);
        }

        if (isset($sampleRule['Host'])) {
            $this->setHost($sampleRule['Host']);
        }

        if (isset($sampleRule['Priority'])) {
            $this->setPriority($sampleRule['Priority']);
        }

        if (isset($sampleRule['ReservoirSize'])) {
            $this->setReservoirSize($sampleRule['ReservoirSize']);
        }

        if (isset($sampleRule['ResourceARN'])) {
            $this->setResourceARN($sampleRule['ResourceARN']);
        }

        if (isset($sampleRule['RuleARN'])) {
            $this->setRuleARN($sampleRule['RuleARN']);
        }

        if (isset($sampleRule['RuleName'])) {
            $this->setName($sampleRule['RuleName']);
        }

        if (isset($sampleRule['ServiceName'])) {
            $this->setServiceName($sampleRule['ServiceName']);
        }

        if (isset($sampleRule['ServiceType'])) {
            $this->setServiceType($sampleRule['ServiceType']);
        }

        if (isset($sampleRule['URLPath'])) {
            $this->setUrlPath($sampleRule['URLPath']);
        }

        return $this;
    }
}
