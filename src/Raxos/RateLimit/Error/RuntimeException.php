<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Error;

/**
 * Class RuntimeException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Error
 * @since 1.0.0
 */
final class RuntimeException extends RateLimitException
{

    public const int ERR_INVALID_PARAMETER = 1;

}
