[![Build Status](https://img.shields.io/travis/patrickkerrigan/php-xray.svg?style=flat-square)](https://travis-ci.org/patrickkerrigan/php-xray) [![Maintainability](https://api.codeclimate.com/v1/badges/548ad6b7c25bef8004cd/maintainability)](https://codeclimate.com/github/patrickkerrigan/php-xray/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/548ad6b7c25bef8004cd/test_coverage)](https://codeclimate.com/github/patrickkerrigan/php-xray/test_coverage) [![PHP 7.0](https://img.shields.io/badge/php-7.0-blue.svg?style=flat-square)](http://php.net/)  [![Packagist](https://img.shields.io/packagist/v/pkerrigan/xray.svg?style=flat-square)](https://packagist.org/packages/pkerrigan/xray)
use Pkerrigan\Xray\Trace;
use Pkerrigan\Xray\HttpTrait;

# pkerrigan\xray
A basic PHP instrumentation library for AWS X-Ray

Until Amazon releases an official PHP SDK for AWS X-Ray this library allows you to add basic instrumentation to PHP applications and report traces via the AWS X-Ray daemon.

Please note that no automatic instrumentation of popular libraries is provided. In order to instrument SQL queries, HTTP requests and/or other services you'll be required to create your own wrappers which start and end tracing segments as appropriate.

## Installation

The recommended way to install this library is using Composer:

```bash
$ composer require pkerrigan/xray ^1.2
```

## Usage

### Creating a trace service

The `TraceService` is a facade that takes care of downloading sampling rules from the AWS console and submitting traces based on the rules you've setup. Therefore, the library will need to know how to download the sampling rules. *Please refer to the [AWS SDK for PHP documentation](https://aws.amazon.com/sdk-for-php/) on how to create and configure a* `\Aws\XRay\XRayClient`:

```php
$xrayClient = new \Aws\XRay\XRayClient($config);
$samplingRuleRepository = new \Pkerrigan\Xray\Sampling\RuleRepository\AwsSdkRuleRepository($xrayClient);
$samplingTargetRepository = new \Pkerrigan\Xray\Sampling\TargetRepository\AwsSdkTargetRepository($xrayClient);
```

Applications will most likely need to download this information very often, so it is recommended (but optional) to cache it. You will need to provide any PSR compliant cache implementation and, since there are [plenty of other libraries](https://packagist.org/providers/psr/simple-cache-implementation) focusing on that, you will have to install and configure your preferred caching implementation yourself. Then wrap the sampling rule repository in a cache implementation:

```php
$cachedSamplingRuleRepository = new \Pkerrigan\Xray\Sampling\RuleRepository\CachedRuleRepository($samplingRuleRepository, $psrCacheImplementation);
```

Next we need to create a state system that PHP can use between requests. This state service should use a PSR cache that is specific to the server it is running on.

```php
$stateManager = new \Pkerrigan\Xray\Sampling\StateManager($psrCacheImplementation);
```

Then we use the state service, the rule repository, and the target repository to create a cache service for the sampler.

```php
$samplerCache = new \Pkerrigan\Xray\Sampling\SamplerCache($cachedSamplingRuleRepository, $samplingTargetRepository, $stateManager);
```


After we use the Sampler Cache to create our public facing Sampler service for the Trace Service

```php
$sampler = new \Pkerrigan\Xray\Sampler($samplerCache);
```

Or simply use helper

```php
$sampler = \Pkerrigan\Xray\XrayHelper::buildSampler($xrayClient, $psrCacheImplementation);
```

Lastly, create the `TraceService`. By default only submitting via the AWS X-Ray daemon is supported:

```php
$traceService = new \Pkerrigan\Xray\TraceService($sampler, new \Pkerrigan\Xray\Submission\DaemonSegmentSubmitter());
```

### Starting a trace

The `Trace` class represents the top-level of an AWS X-Ray trace, and can function as a singleton for easy access from anywhere in your code, including before frameworks and dependency injectors have been initialised.

You should start a trace as early as possible in your request:

```php
use Pkerrigan\Xray\Trace;

Trace::getInstance()
    ->setTraceHeader($_SERVER['HTTP_X_AMZN_TRACE_ID'] ?? null)
    ->setName('app.example.com')
    ->setUrl($_SERVER['REQUEST_URI'])
    ->setMethod($_SERVER['REQUEST_METHOD'])
    ->begin();
```

### Adding a segment to a trace

You can add as many segments to your trace as necessary, including nested segments. To add an SQL query to your trace, you'd do the following:

```php
\Pkerrigan\Xray\Trace::getInstance()
    ->getCurrentSegment()
    ->addSubsegment(
        (new \Pkerrigan\Xray\Segment\SqlSegment())
            ->setName('db.example.com')
            ->setDatabaseType('PostgreSQL')
            ->setQuery($mySanitisedQuery)    // Make sure to remove sensitive data before passing in a query
            ->begin()    
    );


// Run your query here

\Pkerrigan\Xray\Trace::getInstance()
    ->getCurrentSegment()
    ->end();
```

The `getCurrentSegment()` method will always return the most recently opened segment, allowing you to nest as deeply as necessary.

### Ending a trace

At the end of your request, you'll want to end and submit your trace.

```php
\Pkerrigan\Xray\Trace::getInstance()
    ->end()
    ->setResponseCode(http_response_code());

// If need handling sample rules
$traceService->submitTrace(\Pkerrigan\Xray\Trace::getInstance());
// If this project is getting trace id from other server
if (Trace::getInstance()->isSampled()) {
  $submitter = new \Pkerrigan\Xray\Submission\DaemonSegmentSubmitter();
  $submitter->submitSegment(Trace::getInstance());
}
```


### Fallback sampling rule

If any error occurs in AWS regarding retrieving sampling rules, you can optionally configure a default sampling rule that will be provided instead. When creating the sampling rule repository:

```php
// Any options not provided will default to '*'
$fallbackSamplingRule = (new \Pkerrigan\Xray\Sampling\Rule())
	->setFixedRate(0.75)
	->setHttpMethod('GET')
	->setHost('example.com')
	->setServiceName('app.example.com')
	->setServiceType('*')
	->setUrlPath('/my/path')
	->toAWS();

$xrayClient = new \Aws\XRay\XRayClient($config);
$samplingRuleRepository = new \Pkerrigan\Xray\Sampling\RuleRepository\AwsSdkRuleRepository($xrayClient, $fallbackSamplingRule);
```

## Features not yet implemented

* Exception and stack trace support
* Submission of incomplete segments
* Matching sampling rule reservoir and resource ARN
