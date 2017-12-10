<?php
namespace RateGetter;

use GuzzleHttp\Exception\ClientException;
use RateGetter\Domain\RateResponse;
use RateGetter\Exceptions\SymbolNotFoundException;
use RateGetter\Exceptions\SystemException;
use RateGetter\Repository\RepositoryInterface;

class Service
{
    /**
     * @var ResponseParser
     */
    private $responseParser;
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * Service constructor.
     *
     * @param ResponseParser      $responseParser
     * @param RepositoryInterface $repository
     */
    public function __construct(
        ResponseParser $responseParser,
        RepositoryInterface $repository
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
     * @throws \Exception
     */
    public function getStock(
        string $symbol,
        int $from = null,
        int $to = null,
        string $interval = "1m"
    ) : RateResponse {
        $repoResponse = null;
        try {
            $repoResponse = $this->repository->get(
                $symbol,
                $interval,
                $from,
                $to
            );

            return $this->responseParser->parseStock(
                $repoResponse,
                $interval
            );
        } catch (ClientException $ex) {
            if ($ex->getCode() === 404) {
                throw new SymbolNotFoundException($symbol, $ex->getCode());
            }

            throw new SystemException($ex->getMessage(), $ex->getCode());
        }
    }

    public function getCurrency(
        string $symbol,
        int $from = null,
        int $to = null,
        string $interval = "1m"
    ) : RateResponse {
        $yahooCurrency = $symbol."=X";
        return $this->responseParser->parseCurrency(
            $this->repository->get($yahooCurrency, $interval, $from, $to)
        );
    }
}