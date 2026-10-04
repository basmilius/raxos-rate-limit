<a href="https://bas.dev">
    <img src="https://bmcdn.nl/assets/branding/logo.svg" alt="Bas Milius" height="48" />
</a>

---

# Raxos Rate Limit

Quotas stored in Redis for application operations and Raxos Router endpoints.

[Documentation](https://raxos.dev/rate-limit/) | [Packagist](https://packagist.org/packages/raxos/rate-limit) | [Raxos](https://github.com/basmilius/raxos)

- Quotas per second, minute, hour or day.
- Atomic counters with expiration and usage snapshots.
- Router middleware for HTTP 429 responses and rate-limit headers.

## Installation

Requires PHP 8.5 or later. Composer checks the remaining package and extension dependencies declared in [composer.json](composer.json).

```sh
composer require "raxos/rate-limit:^3.3"
```

## Usage

```php
<?php
declare(strict_types=1);

use Raxos\Cache\Redis\RedisCache;
use Raxos\RateLimit\Rate;
use Raxos\RateLimit\RateLimiter;
use Raxos\RateLimit\Store\RedisRateLimiterStore;

require __DIR__ . '/vendor/autoload.php';

$redis = new RedisCache(prefix: 'app:', host: '127.0.0.1', port: 6379);
$store = new RedisRateLimiterStore($redis, keyBase: 'app:ratelimit:');
$limiter = new RateLimiter(Rate::minute(60), $store);

$status = $limiter->getStatus('user:42');
echo json_encode(['limited' => $status->exceeded, 'remainingSeconds' => $status->ttl]);
```

This example needs a running Redis server and the Redis PHP extension through `raxos/cache`. `getStatus()` counts the operation by default; use `increment: false` to inspect usage. `checkLimited()` counts an operation and throws when the quota is exceeded, so choose one counting method per operation. `RedisRateLimiterStore` requires a plain `RedisCache`; configure its key namespace with `keyBase`.

## Documentation

- [Rate limiting core](https://raxos.dev/rate-limit/rate-limiting)
- [Router middleware](https://raxos.dev/rate-limit/router-middleware)

## Testing

Run this library's Pest suite from the Raxos workspace:

```sh
git clone --recurse-submodules https://github.com/basmilius/raxos.git
cd raxos
composer install
vendor/bin/pest --testsuite=rate-limit
```

See [Testing Raxos](https://github.com/basmilius/raxos/blob/main/TESTING.md) for PHP extensions, integration services and coverage commands. The library's [Tests workflow](.github/workflows/tests.yml) also runs in GitHub Actions.

## License

[MIT](LICENSE). Copyright (c) 2017 - present Bas Milius.

See [atomic rate-limit snapshots](https://raxos.dev/rate-limit/atomic-snapshots) for the optional APIs and their lifetime or transport guarantees.
