<?php
namespace RateGetter\Domain;

class AdjustedResponse implements RateResponse
{
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
        $responseData = [];
        $results = $data['chart']['result'];
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
                    $unadjustedClose[0]['unadjclose'][$i],
                    $adjustedClose[0]['adjclose'][$i]
                );
            }

            $this->data[] = new ResultSet(
                new Meta($result['meta']),
                $responseData
            );
        }
    }

    public function getData() : array
    {
        return $this->data;
    }
}
