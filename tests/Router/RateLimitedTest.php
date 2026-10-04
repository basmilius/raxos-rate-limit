<?php
declare(strict_types=1);

use Raxos\Contract\RateLimit\RateLimiterStoreInterface;
use Raxos\Http\{HttpRequest, HttpResponse, HttpResponseCode};
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\RateLimit\{Rate, RateLimitStatus};
use Raxos\RateLimit\Router\RateLimited;

covers(RateLimited::class);

it('runs permitted handlers and short-circuits exceeded requests with rate headers', function (int $operations, bool $exceeded): void {
    $store = $this->createMock(RateLimiterStoreInterface::class);
    $store->expects($this->once())->method('updateOperations')->with('unit:60', 60)->willReturn($operations);
    $store->expects($this->once())->method('getTTL')->with('unit:60')->willReturn(42);
    $middleware = new readonly class(Rate::minute(2), $store) extends RateLimited {
        protected function getKey(): string
        {
            return 'unit';
        }

        protected function getResponse(RateLimitStatus $status): HttpResponse
        {
            return new NoContentHttpResponse()->responseCode(HttpResponseCode::TOO_MANY_REQUESTS);
        }
    };
    $calls = 0;
    $response = $middleware->handle(HttpRequest::create(), function () use (&$calls): HttpResponse {
        $calls++;

        return new NoContentHttpResponse();
    });
    expect($calls)->toBe($exceeded ? 0 : 1)
        ->and($response->responseCode)->toBe($exceeded ? HttpResponseCode::TOO_MANY_REQUESTS : HttpResponseCode::NO_CONTENT)
        ->and($response->headers->get('ratelimit-limit'))->toBe('2')
        ->and($response->headers->get('ratelimit-remaining'))->toBe((string)max(0, 2 - $operations))
        ->and($response->headers->get('ratelimit-reset'))->toBe('42')
        ->and($response->headers->get('retry-after'))->toBe($exceeded ? '42' : null);
})->with([[1, false], [2, false], [3, true]]);
