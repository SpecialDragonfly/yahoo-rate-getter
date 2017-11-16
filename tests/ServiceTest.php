<?php
namespace tests\RateGetter;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use RateGetter\Domain\ErrorResponse;
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
            ->withQuery('symbol=X&period1=123&period2=456&interval=1m');
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
            ->withQuery('symbol=INVALID&period1=123&period2=456&interval=1m');
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
            ->withQuery('symbol=INVALID&period1=123&period2=456&interval=1m');
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
}
