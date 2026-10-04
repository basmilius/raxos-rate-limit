<?php
declare(strict_types=1);

use Raxos\RateLimit\Error\InvalidParameterException;
use Raxos\RateLimit\Rate;

covers(Rate::class);

it('constructs each named interval in seconds', function (Rate $rate, int $interval): void {
    expect($rate->interval)->toBe($interval)->and($rate->quota)->toBe(10);
})->with([[Rate::second(10), 1], [Rate::seconds(3, 10), 3], [Rate::minute(10), 60], [Rate::minutes(3, 10), 180], [Rate::hour(10), 3600], [Rate::hours(3, 10), 10800], [Rate::day(10), 86400], [Rate::days(3, 10), 259200]]);

it('rejects nonpositive quotas and intervals', function (int $interval, int $quota): void {
    expect(fn() => new Rate($interval, $quota))->toThrow(InvalidParameterException::class);
})->with([[0, 1], [-1, 1], [1, 0], [1, -1]]);
