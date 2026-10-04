<?php
declare(strict_types=1);

use Raxos\Cache\Redis\RedisCache;
use Raxos\RateLimit\Error\InvalidParameterException;
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

it('returns one atomic snapshot per operation and does not increment read-only checks', function (): void {
    if (!getenv('RAXOS_REDIS_HOST')) {
        test()->markTestSkipped('Redis is not configured.');
    }
    $redis = new class('unit', getenv('RAXOS_REDIS_HOST'), (int)(getenv('RAXOS_REDIS_PORT') ?: 0)) extends RedisCache {

        public int $evaluations = 0;

        public function eval(string $script, array $keys = [], array $args = []): mixed
        {
            ++$this->evaluations;

            return parent::eval($script, $keys, $args);
        }

    };
    $prefix = 'raxos-unit-snapshot:' . bin2hex(random_bytes(8)) . ':';
    $store = new RedisRateLimiterStore($redis, $prefix);
    $other = new RedisRateLimiterStore(new RedisCache('unit', getenv('RAXOS_REDIS_HOST'), (int)(getenv('RAXOS_REDIS_PORT') ?: 0)), $prefix);

    try {
        expect($store->snapshot('counter', 60, false))->toBe(['operations' => 0, 'ttl' => 0])->and($redis->evaluations)->toBe(1);

        for ($i = 1; $i <= 10; ++$i) {
            $snapshot = ($i % 2 ? $store : $other)->snapshot('counter', 60);
            expect($snapshot['operations'])->toBe($i)->and($snapshot['ttl'])->toBeGreaterThan(0)->toBeLessThanOrEqual(60);
        }
        expect($store->snapshot('counter', 60, false)['operations'])->toBe(10)->and($redis->evaluations)->toBe(7);
        $redis->persist($prefix . 'counter');
        expect($store->snapshot('counter', 60, false))->toBe(['operations' => 10, 'ttl' => 0]);
        $redis->pexpire($prefix . 'counter', 500);
        expect($store->snapshot('counter', 60, false)['ttl'])->toBe(1);
        expect(fn() => $store->snapshot('invalid', 0))->toThrow(InvalidParameterException::class);
    } finally {
        $redis->del($prefix . 'counter');
    }
});
