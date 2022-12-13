<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Store;

use Raxos\Cache\Redis\{RedisCache, RedisCacheException, RedisTaggedCache};
use function ceil;
use function max;

/**
 * Class RedisRateLimiterStore
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Store
 * @since 1.0.0
 */
readonly class RedisRateLimiterStore implements RateLimiterStoreInterface
{

    /**
     * RedisRateLimiterStore constructor.
     *
     * @param RedisCache|RedisTaggedCache $redis
     * @param string $keyBase
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        protected RedisCache|RedisTaggedCache $redis,
        protected string $keyBase = 'rateLimit:'
    )
    {
    }

    /**
     * {@inheritdoc}
     * @throws RedisCacheException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function getOperations(string $key): int
    {
        return (int)$this->redis->get($this->getKey($key)) ?? 0;
    }

    /**
     * {@inheritdoc}
     * @throws RedisCacheException
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
     * @throws RedisCacheException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function updateOperations(string $key, int $interval): int
    {
        $key = $this->getKey($key);
        $operations = $this->redis->incr($key);

        if ($operations === 1) {
            $this->redis->expire($key, $interval);
        }

        return $operations;
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
    protected function getKey(string $key): string
    {
        return $this->keyBase . $key;
    }

}
