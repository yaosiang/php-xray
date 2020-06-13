<?php

namespace Pkerrigan\Xray;

use Pkerrigan\Xray\Segment\HttpTrait;
use Pkerrigan\Xray\Segment\Segment;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 13/05/2018
 */
class Trace extends Segment
{
    use HttpTrait;

    /**
     * @var static
     */
    private static $instance;
    /**
     * @var string
     */
    private $serviceVersion;
    /**
     * @var string
     */
    private $user;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param string $traceHeader
     * @return static
     */
    public function setTraceHeader($traceHeader = null)
    {
        if (is_null($traceHeader)) {
            return $this;
        }

        $parts = explode(';', $traceHeader);

        $variables = array_map(function ($str) {
            return explode('=', $str);
        }, $parts);

        $variables = array_column($variables, 1, 0);

        if (isset($variables['Root'])) {
            $this->setTraceId($variables['Root']);
        }
        $this->setSampled(isset($variables['Sampled']) && $variables['Sampled']);
        $this->setParentId(isset($variables['Parent']) ? $variables['Parent'] : null);

        return $this;
    }

    /**
     * @param string $serviceVersion
     * @return static
     */
    public function setServiceVersion($serviceVersion)
    {
        $this->serviceVersion = $serviceVersion;

        return $this;
    }

    /**
     * @param string $user
     * @return static
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param string $clientIpAddress
     * @return static
     */
    public function setClientIpAddress($clientIpAddress)
    {
        $this->clientIpAddress = $clientIpAddress;

        return $this;
    }

    /**
     * @param string $userAgent
     * @return static
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function begin($samplePercentage = 10)
    {
        parent::begin();

        if (is_null($this->traceId)) {
            $this->generateTraceId();
        }

        if (!$this->isSampled()) {
            $this->sampled = Utils::randomPossibility($samplePercentage);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['http'] = $this->serialiseHttpData();
        $data['service'] = empty($this->serviceVersion) ? null : ['version' => $this->serviceVersion];
        $data['user'] = $this->user;

        return array_filter($data);
    }

    private function generateTraceId()
    {
        $startHex = dechex((int)$this->startTime);
        $uuid = bin2hex(random_bytes(12));

        $this->setTraceId("1-{$startHex}-{$uuid}");
    }
}
