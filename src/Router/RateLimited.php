<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Router;

use Closure;
use Raxos\Contract\RateLimit\RateLimiterStoreInterface;
use Raxos\Contract\Router\MiddlewareInterface;
use Raxos\Http\{HttpRequest, HttpResponse};
use Raxos\RateLimit\{Rate, RateLimiter, RateLimitStatus};
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
    public function handle(HttpRequest $request, Closure $next): HttpResponse
    {
        $status = $this->rateLimiter->getStatus($this->getKey());

        if ($status->exceeded) {
            $response = $this->getResponse($status);
        } else {
            $response = $next($request);
        }

        $response = $response
            ->header('ratelimit-limit', (string)$status->rate->quota)
            ->header('ratelimit-remaining', (string)max(0, $status->rate->quota - $status->operations))
            ->header('ratelimit-reset', (string)$status->ttl);

        if ($status->exceeded) {
            $response = $response->header('retry-after', (string)$status->ttl);
        }

        return $response;
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
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    protected abstract function getResponse(RateLimitStatus $status): HttpResponse;

}
