<?php

namespace Pkerrigan\Xray\SamplingRule;

class SamplingRuleBuilder
{
    /** @var array */
    private $samplingRule = [
        'FixedRate' => 1.0,
        'HTTPMethod' => '*',
        'Host' => '*',
        'Priority' => 1,
        'ReservoirSize' => 1,
        'ResourceARN' => '*',
        'RuleARN' => '*',
        'RuleName' => 'Pkerrigan\\Xray\\SamplingRule',
        'ServiceName' => '*',
        'ServiceType' => '*',
        'URLPath' => '*'
    ];

    public function __construct(array $otherSamplingRule = [])
    {
        // Copy constructor
        foreach (array_keys($this->samplingRule) as $ruleKey) {
            if (isset($otherSamplingRule[$ruleKey])) {
                $this->samplingRule[$ruleKey] = $otherSamplingRule[$ruleKey];
            }
        }
    }

    /**
     * @param int $percentage
     * @return static
     */
    public function setFixedRate($percentage)
    {
        $this->samplingRule['FixedRate'] = $percentage / 100;

        return $this;
    }

    /**
     * @param string $httpMethod
     * @return $this
     */
    public function setHttpMethod($httpMethod)
    {
        $this->samplingRule['HTTPMethod'] = $httpMethod;

        return $this;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->samplingRule['Host'] = $host;

        return $this;
    }

    /**
     * @param string $serviceName
     * @return static
     */
    public function setServiceName($serviceName)
    {
        $this->samplingRule['ServiceName'] = $serviceName;

        return $this;
    }

    /**
     * @param string $serviceType
     * @return static
     */
    public function setServiceType($serviceType)
    {
        $this->samplingRule['ServiceType'] = $serviceType;

        return $this;
    }

    /**
     * @param string $urlPath
     * @return static
     */
    public function setUrlPath($urlPath)
    {
        $this->samplingRule['URLPath'] = $urlPath;

        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        return $this->samplingRule;
    }
}

