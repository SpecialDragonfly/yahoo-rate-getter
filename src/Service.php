<?php
namespace RateGetter;

use RateGetter\Domain\RateResponse;

class Service
{
    /**
     * @var ResponseParser
     */
    private $responseParser;
    /**
     * @var Repository
     */
    private $repository;

    /**
     * Service constructor.
     *
     * @param ResponseParser $responseParser
     * @param Repository     $repository
     */
    public function __construct(
        ResponseParser $responseParser,
        Repository $repository
    ) {
        $this->responseParser = $responseParser;
        $this->repository = $repository;
    }

    /**
     * @param string   $symbol
     * @param int      $from
     * @param int|null $to
     * @param string   $interval
     *
     * @return RateResponse
     */
    public function getStock(
        string $symbol,
        int $from,
        int $to = null,
        string $interval = "1m"
    ) : RateResponse {
        return $this->responseParser->parseStock(
            $this->repository->get($symbol, $interval, $from, $to)
        );
    }

    public function getCurrency(
        string $symbol,
        int $from,
        int $to = null,
        string $interval = "1m"
    ) : RateResponse {
        $yahooCurrency = $symbol."=X";
        return $this->responseParser->parseCurrency(
            $this->repository->get($yahooCurrency, $interval, $from, $to)
        );
    }
}