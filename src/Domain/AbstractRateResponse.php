<?php
namespace RateGetter\Domain;

abstract class AbstractRateResponse implements SuccessResponseInterface
{
    /** @var array */
    protected $data;

    /**
     * Returns an array of the required field in the form:
     * time -> field value
     *
     * @param string $field The field to return data for.
     *
     * @return array
     */
    public function getTimeIndexedField(string $field): array
    {
        if (!in_array($field, $this->getAllowedFieldNames())) {
            return [];
        }

        $response = null;
        if (in_array($field, ['open', 'high', 'low', 'close', 'volume'])) {
            $response = $this->getTimeIndexedOhlc($field);
        }

        if ($field === 'adjclose') {
            $response = $this->getTimeIndexedAdjustedClose();
        }

        if ($field === 'unadjclose') {
            $response = $this->getTimeIndexedUnadjustedClose();
        }

        return $response;
    }

    private function getTimeIndexedOhlc(string $field): array
    {
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

    private function getTimeIndexedAdjustedClose()
    {
        $responseData = [];
        $results = $this->data['chart']['result'];
        foreach ($results as $result) {
            $timestamps = $result['timestamp'];
            $adjustedClose = $result['indicators']['adjclose'];
            for ($i = 0; $i < count($timestamps); $i++) {
                $responseData[$timestamps[$i]] = $adjustedClose[0]['adjclose'][$i];
            }
        }

        return $responseData;
    }

    private function getTimeIndexedUnadjustedClose()
    {
        $responseData = [];
        $results = $this->data['chart']['result'];
        foreach ($results as $result) {
            $timestamps = $result['timestamp'];
            $adjustedClose = $result['indicators']['unadjclose'];
            for ($i = 0; $i < count($timestamps); $i++) {
                $responseData[$timestamps[$i]] = $adjustedClose[0]['unadjclose'][$i];
            }
        }

        return $responseData;
    }

    abstract protected function getAllowedFieldNames() : array;
}