<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Error;

/**
 * Class LimitExceededException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Error
 * @since 1.0.0
 */
final class LimitExceededException extends RateLimitException
{

    public const ERR_EXCEEDED = 1;
    public const ERR_BLOCKED = 2;

}
