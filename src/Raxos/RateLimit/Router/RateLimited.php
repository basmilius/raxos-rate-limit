<?php
declare(strict_types=1);

namespace Raxos\RateLimit\Router;

use Raxos\RateLimit\{Rate, RateLimiter, RateLimitStatus};
use Raxos\RateLimit\Store\RateLimiterStoreInterface;
use Raxos\Router\Attribute\Injected;
use Raxos\Router\Effect\Effect;
use Raxos\Router\MiddlewareInterface;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;
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

    #[Injected]
    public Router $router;

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
    public function handle(): Effect|Response|bool|null
    {
        $status = $this->rateLimiter->getStatus($this->getKey());

        $this->router->responseRegistry->header('RateLimit-Limit', (string)$status->rate->quota);
        $this->router->responseRegistry->header('RateLimit-Remaining', (string)max(0, $status->rate->quota - $status->operations));
        $this->router->responseRegistry->header('RateLimit-Reset', (string)$status->ttl);
        $this->router->responseRegistry->header('Retry-After', (string)$status->ttl);

        if (!$status->exceeded) {
            return true;
        }

        return $this->getResponse($status);
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
     * Gets the response for when a user is rate limited.
     *
     * @param RateLimitStatus $status
     *
     * @return Response
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    protected abstract function getResponse(RateLimitStatus $status): Response;

}
