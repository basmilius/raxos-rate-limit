<?php
declare(strict_types=1);

use Raxos\Cache\Redis\RedisCache;
use Raxos\RateLimit\Store\RedisRateLimiterStore;

covers(RedisRateLimiterStore::class);

it('reads missing, persistent and expiring operation counters safely', function (): void {
    if (getenv('RAXOS_REDIS_HOST') === false) {
        $this->markTestSkipped('Set RAXOS_REDIS_HOST to run Redis integration tests.');
    }
    $redis = new RedisCache('test', getenv('RAXOS_REDIS_HOST'), (int)(getenv('RAXOS_REDIS_PORT') ?: 0));
    $prefix = 'raxos-unit-rate:' . bin2hex(random_bytes(8)) . ':';
    $store = new RedisRateLimiterStore($redis, $prefix);
    try {
        expect($store->getOperations('unit'))->toBe(0)->and($store->getTTL('unit'))->toBe(0);
        $redis->set($prefix . 'unit', '4');
        expect($store->getOperations('unit'))->toBe(4)->and($store->getTTL('unit'))->toBe(0);
        $redis->del($prefix . 'unit');
        expect($store->updateOperations('unit', 60))->toBe(1)->and($store->getTTL('unit'))->toBeGreaterThan(0)->toBeLessThanOrEqual(60);
        $ttl = $redis->pttl($prefix . 'unit');
        expect($store->updateOperations('unit', 90))->toBe(2)->and($redis->pttl($prefix . 'unit'))->toBeLessThanOrEqual($ttl);
    } finally {
        $redis->del($prefix . 'unit');
    }
});
