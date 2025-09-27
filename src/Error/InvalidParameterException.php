<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Error;

use Raxos\Contract\RateLimit\RateLimitExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class InvalidParameterException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Error
 * @since 2.0.0
 */
final class InvalidParameterException extends Exception implements RateLimitExceptionInterface
{

    /**
     * InvalidParameterException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(string $message)
    {
        parent::__construct(
            'rate_limit_invalid_parameter',
            $message
        );
    }

}
