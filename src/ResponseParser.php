<?php
namespace RateGetter;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RateGetter\Domain\ErrorResponse;
use RateGetter\Domain\RateResponse;
use RateGetter\Domain\SuccessResponse;

class ResponseParser
{
    public function __construct()
    {
    }

    /**
     * @param ResponseInterface $response
     *
     * @return RateResponse
     */
    public function parseStock(ResponseInterface $response) : RateResponse
    {
        try {
            $message = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
            if ($response->getStatusCode() === 200) {
                return new SuccessResponse($message, 200);
            }

            return new ErrorResponse($message["chart"]["error"]["description"], $response->getStatusCode());
        } catch (InvalidArgumentException $ex) {
            return new ErrorResponse($ex->getMessage(), 500);
        }
    }
}