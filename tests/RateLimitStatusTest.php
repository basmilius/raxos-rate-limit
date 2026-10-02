<?php
declare(strict_types=1);

use Raxos\RateLimit\{Rate, RateLimitStatus};

covers(RateLimitStatus::class);

it('exceeds the quota only after the permitted operation count', function (int $operations, bool $expected): void {
    $rate = Rate::minute(2);
    $status = new RateLimitStatus($operations, $rate, 42);
    expect($status->exceeded)->toBe($expected)->and($status->operations)->toBe($operations)
        ->and($status->rate)->toBe($rate)->and($status->ttl)->toBe(42);
})->with([[0, false], [1, false], [2, false], [3, true]]);
