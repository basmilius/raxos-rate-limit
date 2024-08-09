<?php
declare(strict_types=1);

namespace Raxos\RateLimit;

use Raxos\RateLimit\Error\LimitExceededException;
use Raxos\RateLimit\Store\RateLimiterStoreInterface;

/**
 * Class RateLimiter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit
 * @since 1.0.0
 */
readonly class RateLimiter
{

    /**
     * RateLimiter constructor.
     *
     * @param Rate $rate
     * @param RateLimiterStoreInterface $store
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(
        protected Rate $rate,
        protected RateLimiterStoreInterface $store
    ) {}

    /**
     * Checks if the rate limit is exceeded and throws a LimitExceededException
     * if it is.
     *
     * @param string $key
     *
     * @throws LimitExceededException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function checkLimited(string $key): void
    {
        $status = $this->getStatus($key);

        if ($status->exceeded) {
            throw new LimitExceededException('Rate limit exceeded.', LimitExceededException::ERR_EXCEEDED);
        }
    }

    /**
     * Gets the rate limit status for the given key.
     *
     * @param string $key
     * @param bool $increment
     *
     * @return RateLimitStatus
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function getStatus(string $key, bool $increment = true): RateLimitStatus
    {
        $key = $this->getKey($key);
        $operations = $this->store->getOperations($key);

        if ($increment && $operations <= $this->rate->quota) {
            $this->store->updateOperations($key, $this->rate->interval);
            ++$operations;
        }

        return new RateLimitStatus($operations, $this->rate, $this->store->getTTL($key));
    }

    /**
     * Gets the rate limiter key.
     *
     * @param string $key
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected function getKey(string $key): string
    {
        $interval = $this->rate->interval;

        return "{$key}:{$interval}";
    }

}
