<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Store;

/**
 * Interface RateLimiterStoreInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Store
 * @since 1.0.0
 */
interface RateLimiterStoreInterface
{

    /**
     * Gets the number of operations for the given key.
     *
     * @param string $key
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function getOperations(string $key): int;

    /**
     * Gets the TTL for the given key.
     *
     * @param string $key
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function getTTL(string $key): int;

    /**
     * Updates the number of operations for the given key.
     *
     * @param string $key
     * @param int $interval
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function updateOperations(string $key, int $interval): int;

}
