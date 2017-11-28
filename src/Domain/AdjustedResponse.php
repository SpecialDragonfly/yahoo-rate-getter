<?php
namespace RateGetter\Domain;

class AdjustedResponse implements RateResponse
{
    const ALLOWED_FIELDS = ['open', 'high', 'low', 'close', 'volume', 'unadjclose', 'adjclose'];
    /**
     * @var array
     */
    private $data;

    /**
     * SuccessResponse constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Returns an array of the required field in the form:
     * time -> field value
     *
     * @param string $field The field to return data for.
     *
     * @return array
     */
    public function getTimeIndexedField(string $field) : array
    {
        if (!in_array($field, static::ALLOWED_FIELDS)) {
            return [];
        }

        $responseData = [];
        $results = $this->data['chart']['result'];
        foreach ($results as $result) {
            $timestamps = $result['timestamp'];
            $quote = $result['indicators']['quote'];
            for ($i = 0; $i < count($timestamps); $i++) {
                $responseData[$timestamps[$i]] = $quote[0][$field][$i];
            }
        }

        return $responseData;
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
            $timestamps = $result['timestamp'];
            $quote = $result['indicators']['quote'];
            $unadjustedClose = $result['indicators']['unadjclose'];
            $adjustedClose = $result['indicators']['adjclose'];
            for ($i = 0; $i < count($timestamps); $i++) {
                $responseData[$timestamps[$i]] = new Technicals(
                    $quote[0]['open'][$i],
                    $quote[0]['high'][$i],
                    $quote[0]['low'][$i],
                    $quote[0]['close'][$i],
                    $quote[0]['volume'][$i],
                    $unadjustedClose[0]['unadjclose'],
                    $adjustedClose[0]['adjclose']
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