<?php
namespace RateGetter\Repository;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;

class Repository implements RepositoryInterface
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
     * @param string   $symbol
     * @param string   $interval
     * @param int|null $from
     * @param int|null $to
     *
     * @return ResponseInterface
     */
    public function get(
        string $symbol,
        string $interval,
        $from = null,
        $to = null
    ) : ResponseInterface {
        $parameters = [
            'symbol' => $symbol,
            'interval' => $interval,
        ];
        if ($from !== null) {
            $parameters['period1'] = $from;
        }
        if ($to !== null) {
            $parameters['period2'] = $to;
        }
        $query = http_build_query($parameters);
        $url = "https://query1.finance.yahoo.com/v8/finance/chart/";

        return $this->client->get(new Uri($url."?".$query));
    }
}