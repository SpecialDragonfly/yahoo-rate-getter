<?php
namespace RateGetter\Domain;

abstract class AbstractRateResponse implements RateResponse
{
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

    abstract protected function getAllowedFieldNames() : array;
}