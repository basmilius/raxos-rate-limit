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
final readonly class Rate
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
    public function __construct(
        public int $interval,
        public int $quota
    )
    {
        if ($this->interval <= 0) {
            throw RuntimeException::invalidParameter('Interval must be greater than 0.');
        }

        if ($this->quota <= 0) {
            throw RuntimeException::invalidParameter('Quota must be greater than 0.');
        }
    }

    /**
     * Returns a rate of one second with the given quota.
     *
     * @param int $quota
     *
     * @return self
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function second(int $quota): self
    {
        return new self(1, $quota);
    }

    /**
     * Returns a rate instance of n seconds with the given quota.
     *
     * @param int $n
     * @param int $quota
     *
     * @return self
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function seconds(int $n, int $quota): self
    {
        return new self($n, $quota);
    }

    /**
     * Returns a rate instance of a minute with the given quota.
     *
     * @param int $quota
     *
     * @return self
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function minute(int $quota): self
    {
        return new self(60, $quota);
    }

    /**
     * Returns a rate instance of n minutes with the given quota.
     *
     * @param int $n
     * @param int $quota
     *
     * @return self
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function minutes(int $n, int $quota): self
    {
        return new self($n * 60, $quota);
    }

    /**
     * Returns a rate instance of an hour with the given quota.
     *
     * @param int $quota
     *
     * @return self
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function hour(int $quota): self
    {
        return new self(3600, $quota);
    }

    /**
     * Returns a rate instance of n hours with the given quota.
     *
     * @param int $n
     * @param int $quota
     *
     * @return self
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function hours(int $n, int $quota): self
    {
        return new self($n * 3600, $quota);
    }

    /**
     * Returns a rate instance of a day with the given quota.
     *
     * @param int $quota
     *
     * @return self
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function day(int $quota): self
    {
        return new self(86400, $quota);
    }

    /**
     * Returns a rate instance of n days with the given quota.
     *
     * @param int $days
     * @param int $quota
     *
     * @return self
     * @throws RuntimeException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public static function days(int $days, int $quota): self
    {
        return new self($days * 86400, $quota);
    }

}
