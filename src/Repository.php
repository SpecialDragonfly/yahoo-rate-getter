<?php
namespace RateGetter;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;

class Repository
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Repository constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $symbol
     * @param int    $from
     * @param int    $to
     * @param string $interval
     *
     * @return ResponseInterface
     */
    public function get(string $symbol, int $from, int $to, string $interval) : ResponseInterface
    {
        $query = http_build_query([
            'symbol' => $symbol,
            'period1' => $from,
            'period2' => $to,
            'interval' => $interval
        ]);
        $url = "https://query1.finance.yahoo.com/v8/finance/chart/";

        return $this->client->get(new Uri($url."?".$query));
    }
}