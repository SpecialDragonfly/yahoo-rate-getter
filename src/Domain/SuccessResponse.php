<?php
namespace RateGetter\Domain;

class SuccessResponse extends AbstractRateResponse
{
    /**
     * SuccessResponse constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData() : array
    {
        return $this->data;
    }

    protected function getAllowedFieldNames(): array
    {
        return ['open', 'high', 'low', 'close', 'volume'];
    }

    public function getParsedResultSet(): array
    {
        $data = [];
        $responseData = [];
        $results = $this->data['chart']['result'];
        foreach ($results as $result) {
            $timestamps = $result['timestamp'];
            $quote = $result['indicators']['quote'];
            for ($i = 0; $i < count($timestamps); $i++) {
                $responseData[$timestamps[$i]] = new Technicals(
                    $quote[0]['open'][$i],
                    $quote[0]['high'][$i],
                    $quote[0]['low'][$i],
                    $quote[0]['close'][$i],
                    $quote[0]['volume'][$i]
                );
            }

            $data[] = new ResultSet(
                new Meta($result['meta']),
                $responseData
            );
        }

        return $data;
    }
}