<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Error;

use Raxos\Foundation\Error\ExceptionId;

/**
 * Class LimitExceededException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Error
 * @since 1.0.17
 */
final class LimitExceededException extends RateLimitException
{

    /**
     * Returns an exceeded exception.
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function exceeded(): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'rate_limit_exceeded',
            'Rate limit exceeded.'
        );
    }

}
