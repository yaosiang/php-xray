<?php

namespace Pkerrigan\Xray;

use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\Sampling\Rule;
use Pkerrigan\Xray\Sampling\SamplerCache;

/**
 * Class SamplerTest
 * @package Pkerrigan\Xray
 */
class SamplerTest extends TestCase
{

    public function testGetMatchedRuleReturnsNull()
    {
        $samplerCache = $this->createMock(SamplerCache::class);

        $rule = (new Rule())
            ->setHost('testing.com');

        $samplerCache
            ->expects($this->once())
            ->method('getAllRules')
            ->willReturn([$rule]);

        $sampler = new Sampler($samplerCache);
        $this->assertNull($sampler->getMatchedRule(
            (new Trace())
                ->begin()
                ->setName('Test')
                ->setUrl('https://test.com')
                ->end()
        ));
    }

    public function testGetMatchedRuleReturnsRule()
    {
        $samplerCache = $this->createMock(SamplerCache::class);

        $rule = (new Rule())
            ->setHost('test.com');

        $samplerCache
            ->expects($this->once())
            ->method('getAllRules')
            ->willReturn([$rule]);

        $sampler = new Sampler($samplerCache);
        $this->assertNotNull($sampler->getMatchedRule(
            (new Trace())
                ->begin()
                ->setName('Test')
                ->setUrl('https://test.com')
                ->end()
        ));
    }

    public function testSerializesWithMatchedRule()
    {
        $samplerCache = $this->createMock(SamplerCache::class);

        $rule = (new Rule())
            ->setName('Rulename')
            ->setHost('test.com');

        $samplerCache
            ->expects($this->once())
            ->method('getAllRules')
            ->willReturn([$rule]);

        $trace = (new Trace())
            ->begin()
            ->setName('Test')
            ->setUrl('https://test.com')
            ->end();

        $sampler = new Sampler($samplerCache);
        $this->assertNotNull($sampler->shouldSample($trace));

        $serialized = $trace->jsonSerialize();

        $this->assertArrayHasKey('xray', $serialized['aws']);
        $this->assertEquals('Rulename', $serialized['aws']['xray']['rule_name']);
    }


    public function testShouldSampleRuleBorrows()
    {
        $samplerCache = $this->createMock(SamplerCache::class);

        $rule = (new Rule())
            ->setHost('test.com');

        $samplerCache
            ->expects($this->once())
            ->method('getAllRules')
            ->willReturn([$rule]);

        $samplerCache
            ->expects($this->once())
            ->method('saveRule');

        $sampler = new Sampler($samplerCache);
        $this->assertTrue($sampler->shouldSample(
            (new Trace())
                ->begin()
                ->setName('Test')
                ->setUrl('https://test.com')
                ->end()
        ));

        $this->assertEquals(1, $rule->getRequestCount());
        $this->assertEquals(0, $rule->getSampledCount());
        $this->assertEquals(1, $rule->getBorrowCount());
    }

    public function testShouldSampleRuleIncreasesSampledCount()
    {
        $samplerCache = $this->createMock(SamplerCache::class);

        $rule = (new Rule())
            ->setHost('test.com')
            ->setFixedRate(1.0)
            ->setSampledCount(2)
            ->setRequestCount(4)
            ->setBorrowCount(2);

        $rule->getReservoir()->loadNewQuota(0, 115, 10);

        $samplerCache
            ->expects($this->exactly(2))
            ->method('getAllRules')
            ->willReturn([$rule]);

        $samplerCache
            ->expects($this->exactly(2))
            ->method('saveRule');

        $sampler = new Sampler($samplerCache);
        $this->assertTrue($sampler->shouldSample(
            (new Trace())
                ->begin()
                ->setName('Test')
                ->setUrl('https://test.com')
                ->end()
        ));

        $this->assertTrue($sampler->shouldSample(
            (new Trace())
                ->begin()
                ->setName('Test')
                ->setUrl('https://test.com')
                ->end()
        ));

        $this->assertEquals(6, $rule->getRequestCount());
        $this->assertEquals(3, $rule->getSampledCount());
        $this->assertEquals(3, $rule->getBorrowCount());
    }
}
