<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Router;

use Closure;
use Raxos\RateLimit\{Rate, RateLimiter, RateLimitStatus};
use Raxos\RateLimit\Store\RateLimiterStoreInterface;
use Raxos\Router\Contract\MiddlewareInterface;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;
use function max;

/**
 * Class RateLimited
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\RateLimit\Router
 * @since 1.0.16
 */
abstract readonly class RateLimited implements MiddlewareInterface
{

    public RateLimiter $rateLimiter;

    /**
     * RateLimited constructor.
     *
     * @param Rate $rate
     * @param RateLimiterStoreInterface $store
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function __construct(
        public Rate $rate,
        public RateLimiterStoreInterface $store
    )
    {
        $this->rateLimiter = new RateLimiter($this->rate, $this->store);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(Request $request, Closure $next): Response
    {
        $status = $this->rateLimiter->getStatus($this->getKey());

        if ($status->exceeded) {
            $response = $this->getResponse($status);
        } else {
            $response = $next($request);
        }

        return $response
            ->withHeader('ratelimit-limit', (string)$status->rate->quota)
            ->withHeader('ratelimit-remaining', (string)max(0, $status->rate->quota - $status->operations))
            ->withHeader('ratelimit-reset', (string)$status->ttl)
            ->withHeader('retry-after', (string)$status->ttl);
    }

    /**
     * Gets the unique user identifier.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    protected abstract function getKey(): string;

    /**
     * Gets the response for when a user is rate-limited.
     *
     * @param RateLimitStatus $status
     *
     * @return Response
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    protected abstract function getResponse(RateLimitStatus $status): Response;

}
