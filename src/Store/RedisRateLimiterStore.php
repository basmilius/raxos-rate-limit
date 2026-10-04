<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Store;

use JetBrains\PhpStorm\Pure;
use Raxos\Cache\Redis\RedisCache;
use Raxos\Contract\Cache\RedisCacheExceptionInterface;
use Raxos\Contract\RateLimit\RateLimiterSnapshotStoreInterface;
use Raxos\RateLimit\Error\InvalidParameterException;
use function ceil;
use function max;

/**
 * Class RedisRateLimiterStore
 *
 * Records fixed-window operation counts and atomically reads their remaining expiry.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Store
 * @since 1.0.0
 */
final readonly class RedisRateLimiterStore implements RateLimiterSnapshotStoreInterface
{
    /**
     * RedisRateLimiterStore constructor.
     *
     * @param RedisCache $redis
     * @param string $keyBase
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        protected RedisCache $redis,
        protected string $keyBase = 'ratelimit:'
    ) {}

    /**
     * {@inheritdoc}
     * @throws RedisCacheExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function getOperations(string $key): int
    {
        return (int)($this->redis->get($this->getKey($key)) ?? 0);
    }

    /**
     * {@inheritdoc}
     * @throws RedisCacheExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function getTTL(string $key): int
    {
        $key = $this->getKey($key);
        $ttl = $this->redis->pttl($key);

        return max((int)ceil($ttl / 1000), 0);
    }

    /**
     * {@inheritdoc}
     * @throws RedisCacheExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function updateOperations(
        string $key,
        int $interval
    ): int
    {
        $key = $this->getKey($key);

        return (int)$this->redis->eval(
            <<<'LUA'
            local ops = redis.call('INCR', KEYS[1])
            if ops == 1 then
                redis.call('EXPIRE', KEYS[1], ARGV[1])
            end
            return ops
            LUA,
            [$key],
            [$interval]
        );
    }

    /**
     * Reads count and remaining expiry atomically, with an optional increment in the same Redis script.
     *
     * @param string $key
     * @param int $interval
     * @param bool $increment
     *
     * @return array{operations:int, ttl:int}
     * @throws RedisCacheExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public function snapshot(
        string $key,
        int $interval,
        bool $increment = true
    ): array
    {
        if ($interval <= 0) {
            throw new InvalidParameterException('interval');
        }

        [$operations, $ttl] = $this->redis->eval(
            <<<'LUA'
            local operations
            if ARGV[2] == '1' then
                operations = redis.call('INCR', KEYS[1])
                if operations == 1 then redis.call('EXPIRE', KEYS[1], ARGV[1]) end
            else
                operations = tonumber(redis.call('GET', KEYS[1]) or 0)
            end
            return {operations, redis.call('PTTL', KEYS[1])}
            LUA,
            [$this->getKey($key)],
            [$interval, $increment ? 1 : 0]
        );

        return ['operations' => (int)$operations, 'ttl' => max((int)ceil($ttl / 1000), 0)];
    }

    /**
     * Gets the redis key for the given rate limiter key.
     *
     * @param string $key
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    #[Pure]
    protected function getKey(string $key): string
    {
        return $this->keyBase . $key;
    }
}
