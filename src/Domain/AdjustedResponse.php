<?php
namespace RateGetter\Domain;

use RateGetter\Exceptions\InvalidDataException;

class AdjustedResponse extends AbstractRateResponse
{
    /**
     * SuccessResponse constructor.
     *
     * @param array $data
     *
     * @throws InvalidDataException
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $results = $this->data['chart']['result'];
        foreach ($results as $result) {
            if (!array_key_exists('timestamp', $result)) {
                throw new InvalidDataException(
                    "No timeseries data in response"
                );
            }
        }
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    protected function getAllowedFieldNames() : array
    {
        return ['open', 'high', 'low', 'close', 'volume', 'unadjclose', 'adjclose'];
    }

    /**
     * @return ResultSet[]
     */
    public function getParsedResultSet() : array
    {
        $data = [];
        $responseData = [];
        $results = $this->data['chart']['result'];
        foreach ($results as $result) {
            if (!isset($result['timestamp'])) {
                continue;
            }
            $timestamps = $result['timestamp'];
            $indicators = $result['indicators'];
            $quote = $indicators['quote'];
            $unadjustedClose = $indicators['unadjclose'];
            $adjustedClose = $indicators['adjclose'];
            for ($i = 0; $i < count($timestamps); $i++) {
                $responseData[$timestamps[$i]] = new Technicals(
                    $quote[0]['open'][$i],
                    $quote[0]['high'][$i],
                    $quote[0]['low'][$i],
                    $quote[0]['close'][$i],
                    $quote[0]['volume'][$i],
                    $unadjustedClose[0]['unadjclose'][$i],
                    $adjustedClose[0]['adjclose'][$i]
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
