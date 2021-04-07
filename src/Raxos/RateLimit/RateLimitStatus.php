<?php
declare(strict_types=1);

namespace Raxos\RateLimit;

/**
 * Class RateLimitStatus
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit
 * @since 1.0.0
 */
final class RateLimitStatus
{

    /**
     * RateLimitStatus constructor.
     *
     * @param int $operations
     * @param Rate $rate
     * @param int $ttl
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(private int $operations, private Rate $rate, private int $ttl)
    {
    }

    /**
     * Gets the current operations.
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getOperations(): int
    {
        return $this->operations;
    }

    /**
     * Gets the rate.
     *
     * @return Rate
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getRate(): Rate
    {
        return $this->rate;
    }

    /**
     * Gets the ttl.
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function getTTL(): int
    {
        return $this->ttl;
    }

    /**
     * Returns TRUE if the rate limit is exceeded.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function isExceeded(): bool
    {
        return $this->operations >= $this->rate->getQuota();
    }

}
