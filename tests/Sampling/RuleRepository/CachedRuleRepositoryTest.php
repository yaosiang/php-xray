<?php

namespace Pkerrigan\Xray\Sampling\RuleRepository;

use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\Sampling\CacheError;
use Psr\SimpleCache\CacheInterface;

class CachedSamplingRuleRepositoryTest extends TestCase
{
    public function testGetAllWhenCacheExists()
    {
        $expected = [
            [ 'fake_sampling_rule' ]
        ];

        $repository = $this->createMock(RuleRepository::class);
        $repository->expects($this->never())
            ->method('getAll');

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $cache->expects($this->once())
            ->method('get')
            ->willReturn($expected);
        $cache->expects($this->never())
            ->method('set');

        $cachedRepository = new CachedRuleRepository($repository, $cache);
        $this->assertEquals($expected, $cachedRepository->getAll());
    }

    public function testGetAllWhenCacheNotExists()
    {
        $expected = [
            [ 'fake_sampling_rule' ]
        ];

        $repository = $this->createMock(RuleRepository::class);
        $repository->expects($this->once())
            ->method('getAll')
            ->willReturn($expected);

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('has')
            ->willReturn(false);
        $cache->expects($this->never())
            ->method('get');
        $cache->expects($this->once())
            ->method('set');

        $this->expectException(CacheError::class);
        $this->expectExceptionMessage('Failed to save sampling rules to the cache');

        $cachedRepository = new CachedRuleRepository($repository, $cache);
        $this->assertEquals($expected, $cachedRepository->getAll());
    }
}
