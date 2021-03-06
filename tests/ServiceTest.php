<?php
namespace tests\RateGetter;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use RateGetter\Domain\AdjustedResponse;
use RateGetter\Domain\ErrorResponse;
use RateGetter\Domain\ResultSet;
use RateGetter\Domain\SuccessResponse;
use RateGetter\Repository;
use RateGetter\ResponseParser;
use RateGetter\Service;

class ServiceTest extends TestCase
{
    public function testGetStock()
    {
        $symbol = 'X';
        $from = 123;
        $to = 456;
        $yahooResponse = <<<RESP
{"chart":{
    "result":[
        {"meta":{
            "currency":"GBp",
            "symbol":"BARC.L",
            "exchangeName":"LSE",
            "instrumentType":"EQUITY",
            "firstTradeDate":583740900,
            "gmtoffset":0,
            "timezone":"GMT",
            "exchangeTimezoneName":"Europe/London",
            "previousClose":182.4,
            "scale":3,
            "currentTradingPeriod":{
                "pre":{"timezone":"GMT","end":1510819200,"start":1510816500,"gmtoffset":0},
                "regular":{"timezone":"GMT","end":1510849800,"start":1510819200,"gmtoffset":0},
                "post":{"timezone":"GMT","end":1510852500,"start":1510849800,"gmtoffset":0}
            },
            "tradingPeriods":[[{"timezone":"GMT","end":1510849800,"start":1510819200,"gmtoffset":0}]],
            "dataGranularity":"1m",
            "validRanges":["1d","5d","1mo","3mo","6mo","1y","2y","5y","10y","ytd","max"]
        },
        "timestamp":[
            1510835520,1510835580,1510835640,1510835700,1510835760,1510835820,1510835880,1510835940,1510836000,
            1510836060,1510836120,1510836180,1510836240,1510836300
        ],
        "indicators":{
            "quote":[{
                "volume":[0,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "high":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "open":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "low":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "close":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null]
            }]
        }
    }],
    "error":null
}}
RESP;

        $uri = (new Uri())
            ->withScheme('https')
            ->withHost('query1.finance.yahoo.com')
            ->withPath('/v8/finance/chart/')
            ->withQuery('symbol=X&interval=1m&period1=123&period2=456');
        $clientResponse = new Response(200, [], $yahooResponse);

        $mockClient = $this->getMockBuilder(Client::class)
                           ->setMethods(['get'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $responseParser = new ResponseParser();
        $repository = new Repository($mockClient);
        $service = new Service($responseParser, $repository);

        $mockClient
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->willReturn($clientResponse);

        /** @var SuccessResponse $response */
        $response = $service->getStock($symbol, $from, $to);
        $this->assertInstanceOf(SuccessResponse::class, $response, "Got: ".get_class($response));
        $this->assertInternalType('array', $response->getData());
        $data = $response->getData();
        $this->assertCount(1, $data, "Response didn't contain a result set");
    }

    public function testUnrecognisedSymbol()
    {
        $symbol = 'INVALID';
        $from = 123;
        $to = 456;
        $yahooResponse = <<<RESP
{"chart":{"result":null,"error":{"code":"Not Found","description":"No data found, symbol may be delisted"}
RESP;

        $uri = (new Uri())
            ->withScheme('https')
            ->withHost('query1.finance.yahoo.com')
            ->withPath('/v8/finance/chart/')
            ->withQuery('symbol=INVALID&interval=1m&period1=123&period2=456');
        $clientResponse = new Response(404, [], $yahooResponse);

        $mockClient = $this->getMockBuilder(Client::class)
                           ->setMethods(['get'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $responseParser = new ResponseParser();
        $repository = new Repository($mockClient);
        $service = new Service($responseParser, $repository);

        $mockClient
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->willReturn($clientResponse);

        /** @var SuccessResponse $response */
        $response = $service->getStock($symbol, $from, $to);
        $this->assertInstanceOf(ErrorResponse::class, $response, "Got: ".get_class($response));
    }

    public function testInvalidInterval()
    {
        $symbol = 'INVALID';
        $from = 123;
        $to = 456;
        $yahooResponse = <<<RESP
{"chart":{
    "result":null,
    "error":{
        "code":"Bad Request",
        "description":"Invalid input - interval=7d is not supported. Valid intervals: [1m, 2m, 5m, 15m, 30m, 60m, 90m, 1h, 1d, 5d, 1wk, 1mo, 3mo]"
    }
}}
RESP;

        $uri = (new Uri())
            ->withScheme('https')
            ->withHost('query1.finance.yahoo.com')
            ->withPath('/v8/finance/chart/')
            ->withQuery('symbol=INVALID&interval=1m&period1=123&period2=456');
        $clientResponse = new Response(400, [], $yahooResponse);

        $mockClient = $this->getMockBuilder(Client::class)
                           ->setMethods(['get'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $responseParser = new ResponseParser();
        $repository = new Repository($mockClient);
        $service = new Service($responseParser, $repository);

        $mockClient
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->willReturn($clientResponse);

        /** @var SuccessResponse $response */
        $response = $service->getStock($symbol, $from, $to);
        $this->assertInstanceOf(ErrorResponse::class, $response, "Got: ".get_class($response));
    }

    public function testGetAllDataUntilTime()
    {
        $symbol = 'X';
        $to = 456;
        $yahooResponse = <<<RESP
{"chart":{
    "result":[
        {"meta":{
            "currency":"GBp",
            "symbol":"BARC.L",
            "exchangeName":"LSE",
            "instrumentType":"EQUITY",
            "firstTradeDate":583740900,
            "gmtoffset":0,
            "timezone":"GMT",
            "exchangeTimezoneName":"Europe/London",
            "previousClose":182.4,
            "scale":3,
            "currentTradingPeriod":{
                "pre":{"timezone":"GMT","end":1510819200,"start":1510816500,"gmtoffset":0},
                "regular":{"timezone":"GMT","end":1510849800,"start":1510819200,"gmtoffset":0},
                "post":{"timezone":"GMT","end":1510852500,"start":1510849800,"gmtoffset":0}
            },
            "tradingPeriods":[[{"timezone":"GMT","end":1510849800,"start":1510819200,"gmtoffset":0}]],
            "dataGranularity":"1m",
            "validRanges":["1d","5d","1mo","3mo","6mo","1y","2y","5y","10y","ytd","max"]
        },
        "timestamp":[
            1510835520,1510835580,1510835640,1510835700,1510835760,1510835820,1510835880,1510835940,1510836000,
            1510836060,1510836120,1510836180,1510836240,1510836300
        ],
        "indicators":{
            "quote":[{
                "volume":[0,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "high":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "open":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "low":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "close":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null]
            }]
        }
    }],
    "error":null
}}
RESP;

        $uri = (new Uri())
            ->withScheme('https')
            ->withHost('query1.finance.yahoo.com')
            ->withPath('/v8/finance/chart/')
            ->withQuery('symbol=X&interval=1m&period2=456');
        $clientResponse = new Response(200, [], $yahooResponse);

        $mockClient = $this->getMockBuilder(Client::class)
                           ->setMethods(['get'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $responseParser = new ResponseParser();
        $repository = new Repository($mockClient);
        $service = new Service($responseParser, $repository);

        $mockClient
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->willReturn($clientResponse);

        /** @var SuccessResponse $response */
        $response = $service->getStock($symbol, null, $to);
        $this->assertInstanceOf(SuccessResponse::class, $response, "Got: ".get_class($response));
        $this->assertInternalType('array', $response->getData());
        $data = $response->getParsedResultSet();
        $this->assertCount(1, $data, "Response didn't contain a result set");
    }

    public function testGetAllDataSinceTime()
    {
        $symbol = 'X';
        $from = 456;
        $yahooResponse = <<<RESP
{"chart":{
    "result":[
        {"meta":{
            "currency":"GBp",
            "symbol":"BARC.L",
            "exchangeName":"LSE",
            "instrumentType":"EQUITY",
            "firstTradeDate":583740900,
            "gmtoffset":0,
            "timezone":"GMT",
            "exchangeTimezoneName":"Europe/London",
            "previousClose":182.4,
            "scale":3,
            "currentTradingPeriod":{
                "pre":{"timezone":"GMT","end":1510819200,"start":1510816500,"gmtoffset":0},
                "regular":{"timezone":"GMT","end":1510849800,"start":1510819200,"gmtoffset":0},
                "post":{"timezone":"GMT","end":1510852500,"start":1510849800,"gmtoffset":0}
            },
            "tradingPeriods":[[{"timezone":"GMT","end":1510849800,"start":1510819200,"gmtoffset":0}]],
            "dataGranularity":"1m",
            "validRanges":["1d","5d","1mo","3mo","6mo","1y","2y","5y","10y","ytd","max"]
        },
        "timestamp":[
            1510835520,1510835580,1510835640,1510835700,1510835760,1510835820,1510835880,1510835940,1510836000,
            1510836060,1510836120,1510836180,1510836240,1510836300
        ],
        "indicators":{
            "quote":[{
                "volume":[0,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "high":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "open":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "low":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null],
                "close":[185.65020751953125,null,null,null,null,null,null,null,null,null,null,null,null,null]
            }]
        }
    }],
    "error":null
}}
RESP;

        $uri = (new Uri())
            ->withScheme('https')
            ->withHost('query1.finance.yahoo.com')
            ->withPath('/v8/finance/chart/')
            ->withQuery('symbol=X&interval=1m&period1=456');
        $clientResponse = new Response(200, [], $yahooResponse);

        $mockClient = $this->getMockBuilder(Client::class)
                           ->setMethods(['get'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $responseParser = new ResponseParser();
        $repository = new Repository($mockClient);
        $service = new Service($responseParser, $repository);

        $mockClient
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->willReturn($clientResponse);

        /** @var SuccessResponse $response */
        $response = $service->getStock($symbol, $from);
        $this->assertInstanceOf(SuccessResponse::class, $response, "Got: ".get_class($response));
        $this->assertInternalType('array', $response->getData());
        $data = $response->getParsedResultSet();
        $this->assertCount(1, $data, "Response didn't contain a result set");
    }

    public function testDailyResultsSet()
    {
        $symbol = 'X';
        $from = 123;
        $to = 456;
        $yahooResponse = <<<RESP
{"chart": {
    "result": [{
        "meta": {
            "currency": "GBp",
            "symbol": "BARC.L",
            "exchangeName": "LSE",
            "instrumentType": "EQUITY",
            "firstTradeDate": 583740900,
            "gmtoffset": 0,
            "timezone": "GMT",
            "exchangeTimezoneName": "Europe/London",
            "currentTradingPeriod": {
                "pre": {"timezone": "GMT", "end": 1511510400, "start": 1511507700, "gmtoffset": 0},
                "regular": {"timezone": "GMT", "end": 1511541000, "start": 1511510400, "gmtoffset": 0},
                "post": {"timezone": "GMT", "end": 1511543700, "start": 1511541000, "gmtoffset": 0}
            },
            "dataGranularity": "1d",
            "validRanges": ["1d", "5d", "1mo", "3mo", "6mo", "1y", "2y", "5y", "10y", "ytd", "max"]
        },
        "timestamp": [1511424000, 1511510400],
        "indicators": {
            "quote": [{
                "high": [189.85000610351562, 189.85000610351562],
                "open": [187.8000030517578, 189],
                "volume": [22932407, 69658322],
                "low": [187.75, 187.8000030517578],
                "close": [188.75, 189.35000610351562]
            }],
            "unadjclose": [{
                "unadjclose": [188.75, 189.35000610351562]
            }],
            "adjclose": [{
                "adjclose": [188.75, 189.35000610351562]
            }]
        }
    }],
    "error": null
}}
RESP;

        $uri = (new Uri())
            ->withScheme('https')
            ->withHost('query1.finance.yahoo.com')
            ->withPath('/v8/finance/chart/')
            ->withQuery('symbol=X&interval=1d&period1=123&period2=456');
        $clientResponse = new Response(200, [], $yahooResponse);

        $mockClient = $this->getMockBuilder(Client::class)
                           ->setMethods(['get'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $responseParser = new ResponseParser();
        $repository = new Repository($mockClient);
        $service = new Service($responseParser, $repository);

        $mockClient
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->willReturn($clientResponse);

        /** @var AdjustedResponse $response */
        $response = $service->getStock($symbol, $from, $to, "1d");
        $this->assertInstanceOf(AdjustedResponse::class, $response, "Got: ".get_class($response));
        $this->assertInternalType('array', $response->getData());
        $data = $response->getParsedResultSet();
        $this->assertCount(1, $data, "Response didn't contain a result set");

        /** @var ResultSet $resultSet */
        $resultSet = $data[0];
        $timestamps = $resultSet->getTimes();
        foreach ($timestamps as $timestamp) {
            $technicals = $resultSet->getDataForTime($timestamp);
            $this->assertNotNull($technicals->getUnadjustedClose());
            $this->assertNotNull($technicals->getAdjustedClose());
        }
    }

    public function testResponseWithNoQuote()
    {
        $symbol = "X";
        $from = 123;
        $to = 456;
        $yahooResponse = <<<RESP
{"chart":{
    "result":[{
        "meta":{
            "currency":"USD",
            "symbol":"ACIA.L",
            "exchangeName":"LSE",
            "instrumentType":"EQUITY",
            "firstTradeDate":null,
            "gmtoffset":0,
            "timezone":"GMT",
            "exchangeTimezoneName":"Europe/London",
            "chartPreviousClose":0.0,
            "currentTradingPeriod":{
                "pre":{"timezone":"GMT","end":1512460800,"start":1512458100,"gmtoffset":0},
                "regular":{"timezone":"GMT","end":1512491400,"start":1512460800,"gmtoffset":0},
                "post":{"timezone":"GMT","end":1512494100,"start":1512491400,"gmtoffset":0}
            },
            "dataGranularity":"3mo",
            "validRanges":["1d","5d"]
        },
        "indicators":{
            "quote":[{}],
            "unadjclose":[{}],
            "adjclose":[{}]
        }
    }],
    "error":null
}}
RESP;

        $uri = (new Uri())
            ->withScheme('https')
            ->withHost('query1.finance.yahoo.com')
            ->withPath('/v8/finance/chart/')
            ->withQuery('symbol=X&interval=1d&period1=123&period2=456');
        $clientResponse = new Response(200, [], $yahooResponse);

        $mockClient = $this->getMockBuilder(Client::class)
                           ->setMethods(['get'])
                           ->disableOriginalConstructor()
                           ->getMock();
        $responseParser = new ResponseParser();
        $repository = new Repository($mockClient);
        $service = new Service($responseParser, $repository);

        $mockClient
            ->expects($this->once())
            ->method('get')
            ->with($uri)
            ->willReturn($clientResponse);

        try {
            $service->getStock($symbol, $from, $to, "1d");
            $this->fail();
        } catch (\Exception $ex) {
            $this->assertEquals("No timeseries data in response", $ex->getMessage());
        }
    }
}
