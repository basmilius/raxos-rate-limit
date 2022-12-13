<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Router;

use Raxos\RateLimit\Rate;
use Raxos\RateLimit\RateLimiter;
use Raxos\RateLimit\RateLimitStatus;
use Raxos\RateLimit\Store\RateLimiterStoreInterface;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Middleware\Middleware;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;
use function max;

/**
 * Class RateLimitMiddleware
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Router
 * @since 1.0.0
 */
abstract class RateLimitMiddleware extends Middleware
{

    protected readonly RateLimiter $rateLimiter;

    /**
     * RateLimitMiddleware constructor.
     *
     * @param Router $router
     * @param Rate $rate
     * @param RateLimiterStoreInterface $store
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function __construct(Router $router, Rate $rate, RateLimiterStoreInterface $store)
    {
        parent::__construct($router);

        $this->rateLimiter = new RateLimiter($rate, $store);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    public function handle(): Effect|Response|bool|null
    {
        $status = $this->rateLimiter->getStatus($this->getKey());

        $this->header('RateLimit-Limit', (string)$status->rate->quota);
        $this->header('RateLimit-Remaining', (string)max(0, $status->rate->quota - $status->operations));
        $this->header('RateLimit-Reset', (string)$status->ttl);
        $this->header('Retry-After', (string)$status->ttl);

        if (!$status->isExceeded()) {
            return true;
        }

        return $this->getResponse($status);
    }

    /**
     * Gets the unique user identifier.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected abstract function getKey(): string;

    /**
     * Gets the response for when a user is rate limited.
     *
     * @param RateLimitStatus $status
     *
     * @return Response
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.0
     */
    protected abstract function getResponse(RateLimitStatus $status): Response;

}
