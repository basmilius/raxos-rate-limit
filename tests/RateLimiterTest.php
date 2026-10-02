<?php
declare(strict_types=1);

use Raxos\Cache\Redis\RedisCache;
use Raxos\RateLimit\Error\InvalidParameterException;
use Raxos\RateLimit\Rate;
use Raxos\RateLimit\RateLimiter;
use Raxos\RateLimit\Store\RedisRateLimiterStore;

it('rejects invalid quotas and intervals', function (): void {
    expect(fn() => new Rate(0, 1))->toThrow(InvalidParameterException::class);
    expect(fn() => new Rate(1, 0))->toThrow(InvalidParameterException::class);
});

it('requires a Redis store that supports atomic commands', function (): void {
    $redis = new RedisCache('test', connect: false);
    expect(fn() => new RedisRateLimiterStore($redis->tags(['rate'])))->toThrow(TypeError::class);
});

it('increments atomically and retains the original expiry across requests', function (): void {
    if (getenv('RAXOS_REDIS_HOST') === false) {
        $this->markTestSkipped('Set RAXOS_REDIS_HOST to run Redis integration tests.');
    }
    $redis = new RedisCache('test', getenv('RAXOS_REDIS_HOST'), (int)(getenv('RAXOS_REDIS_PORT') ?: 0));
    $base = 'raxos-rate-test:' . bin2hex(random_bytes(8)) . ':';
    try {
        $limiter = new RateLimiter(new Rate(60, 2), new RedisRateLimiterStore($redis, $base));
        expect($limiter->getStatus('client')->operations)->toBe(1);
        $firstTtl = $redis->pttl($base . 'client:60');
        expect($limiter->getStatus('client')->exceeded)->toBeFalse();
        expect($limiter->getStatus('client')->exceeded)->toBeTrue();
        expect($limiter->getStatus('client', false)->operations)->toBe(3);
        expect($redis->pttl($base . 'client:60'))->toBeLessThanOrEqual($firstTtl);
    } finally {
        $redis->del($base . 'client:60');
    }
});

it('uses the interval-specific key and throws only after the quota is exceeded', function (): void {
    $store = $this->createMock(Raxos\Contract\RateLimit\RateLimiterStoreInterface::class);
    $store->expects($this->exactly(3))->method('updateOperations')->with('client:60', 60)->willReturnOnConsecutiveCalls(1, 2, 3);
    $store->expects($this->exactly(3))->method('getTTL')->with('client:60')->willReturn(42);
    $limiter = new RateLimiter(new Rate(60, 2), $store);
    $limiter->checkLimited('client');
    $limiter->checkLimited('client');
    expect(fn(): mixed => $limiter->checkLimited('client'))->toThrow(Raxos\RateLimit\Error\LimitExceededException::class);
});

it('reads a status without incrementing operations', function (): void {
    $store = $this->createMock(Raxos\Contract\RateLimit\RateLimiterStoreInterface::class);
    $store->expects($this->never())->method('updateOperations');
    $store->expects($this->once())->method('getOperations')->with('client:30')->willReturn(0);
    $store->method('getTTL')->willReturn(0);
    $status = new RateLimiter(new Rate(30, 1), $store)->getStatus('client', false);
    expect($status->operations)->toBe(0)->and($status->exceeded)->toBeFalse()->and($status->ttl)->toBe(0);
});
