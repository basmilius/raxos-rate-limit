<?php
declare(strict_types=1);

namespace Raxos\RateLimit;

use Raxos\RateLimit\Error\RuntimeException;

/**
 * Class Rate
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit
 * @since 1.0.0
 */
class Rate
{

    /**
     * Rate constructor.
     *
     * @param int $interval
     * @param int $quota
     *
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public final function __construct(
        public readonly int $interval,
        public readonly int $quota
    )
    {
        if ($this->interval <= 0) {
            throw new RuntimeException('Interval must be greather than 0.', RuntimeException::ERR_INVALID_PARAMETER);
        }

        if ($this->quota <= 0) {
            throw new RuntimeException('Quota must be greather than 0.', RuntimeException::ERR_INVALID_PARAMETER);
        }
    }

    /**
     * Returns a rate of one second with the given quota.
     *
     * @param int $quota
     *
     * @return static
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function second(int $quota): static
    {
        return new static(1, $quota);
    }

    /**
     * Returns a rate instance of n seconds with the given quota.
     *
     * @param int $n
     * @param int $quota
     *
     * @return static
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function seconds(int $n, int $quota): static
    {
        return new static($n, $quota);
    }

    /**
     * Returns a rate instance of a minute with the given quota.
     *
     * @param int $quota
     *
     * @return static
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function minute(int $quota): static
    {
        return new static(60, $quota);
    }

    /**
     * Returns a rate instance of n minutes with the given quota.
     *
     * @param int $n
     * @param int $quota
     *
     * @return static
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function minutes(int $n, int $quota): static
    {
        return new static($n * 60, $quota);
    }

    /**
     * Returns a rate instance of an hour with the given quota.
     *
     * @param int $quota
     *
     * @return static
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function hour(int $quota): static
    {
        return new static(3600, $quota);
    }

    /**
     * Returns a rate instance of n hours with the given quota.
     *
     * @param int $n
     * @param int $quota
     *
     * @return static
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function hours(int $n, int $quota): static
    {
        return new static($n * 3600, $quota);
    }

    /**
     * Returns a rate instance of a day with the given quota.
     *
     * @param int $quota
     *
     * @return static
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function day(int $quota): static
    {
        return new static(86400, $quota);
    }

    /**
     * Returns a rate instance of n days with the given quota.
     *
     * @param int $days
     * @param int $quota
     *
     * @return static
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function days(int $days, int $quota): static
    {
        return new static($days * 86400, $quota);
    }

}
