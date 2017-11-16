# Yahoo Rate Getter

A library for getting the rates for stocks from Yahoo

# Usage

```php
$client = new GuzzleHttp\Client();
$repository = new RateGetter\Repository($client);
$responseParser = new RateGetter\ResponseParser();
$service = new RateGetter\Service($responseParser, $repository);
$response = $service->getStock("BARC.L", 1509619468, 1509620068, "1m");
```
