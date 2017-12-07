<?php
namespace RateGetter;

use Psr\Http\Message\ResponseInterface;

interface RepositoryInterface
{
    public function get(
        string $symbol,
        string $interval,
        $from = null,
        $to = null
    ) : ResponseInterface;
}