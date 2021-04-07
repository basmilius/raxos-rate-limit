<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Error;

use Raxos\Foundation\Error\RaxosException;

/**
 * Class RateLimitException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Error
 * @since 1.0.0
 */
abstract class RateLimitException extends RaxosException
{
}
