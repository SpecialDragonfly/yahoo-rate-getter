<?php
namespace RateGetter\Repository;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class FileCachedRepository implements RepositoryInterface
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var string
     */
    private $dataStore;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger, Client $client, string $dataStore)
    {
        $this->client = $client;
        $this->dataStore = $dataStore;
        $this->logger = $logger;
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

        $filename = $this->dataStore.$symbol."|".$interval."|".$from."|".$to;
        $now = microtime(true);
        if (file_exists($filename)) {
            $this->logger->debug("Getting symbol (c): ".$symbol." in ".(microtime(true) - $now));
            return new Response(200, [], file_get_contents($filename));
        }

        $data = $this->client->get(new Uri($url."?".$query));
        $this->logger->debug("Getting symbol (d): ".$symbol." in ".(microtime(true) - $now)."s");

        file_put_contents($filename, $data->getBody()->getContents());
        $data->getBody()->rewind();

        return $data;
    }
}