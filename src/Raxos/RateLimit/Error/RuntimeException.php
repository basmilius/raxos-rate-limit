<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Error;

use Raxos\Foundation\Error\ExceptionId;

/**
 * Class RuntimeException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Error
 * @since 1.0.17
 */
final class RuntimeException extends RateLimitException
{

    /**
     * Returns an invalid parameter exception.
     *
     * @param string $message
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public static function invalidParameter(string $message): self
    {
        return new self(
            ExceptionId::for(__METHOD__),
            'rate_limit_invalid_parameter',
            $message
        );
    }

}
