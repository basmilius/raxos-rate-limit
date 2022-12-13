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
final readonly class RateLimitStatus
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
    public function __construct(
        public int $operations,
        public Rate $rate,
        public int $ttl
    )
    {
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
        return $this->operations > $this->rate->quota;
    }

}
