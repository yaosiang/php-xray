<?php

namespace Pkerrigan\Xray\Segment;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 14/05/2018
 */
trait HttpTrait
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var string
     */
    protected $clientIpAddress;
    /**
     * @var string
     */
    protected $userAgent;
    /**
     * @var int
     */
    protected $responseCode;
    /**
     * @var int
     */
    protected $contentLength;

    /**
     * @param string $url
     * @return static
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $method
     * @return static
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param int $responseCode
     * @return static
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * @param int $contentLength
     * @return HttpTrait
     */
    public function setContentLength($contentLength)
    {
        $this->contentLength = $contentLength;
        return $this;
    }

    /**
     * @return array
     */
    protected function serialiseHttpData()
    {
        return [
            'request' => array_filter([
                'url' => $this->url,
                'method' => $this->method,
                'client_ip' => $this->clientIpAddress,
                'user_agent' => $this->userAgent
            ]),
            'response' => array_filter([
                'status' => $this->responseCode,
                'content_length' => $this->contentLength,
            ])
        ];
    }
}
