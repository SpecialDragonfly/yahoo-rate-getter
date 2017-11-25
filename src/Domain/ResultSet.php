<?php
namespace RateGetter\Domain;

class ResultSet
{
    /**
     * @var Meta
     */
    private $meta;
    /**
     * @var array
     */
    private $timeSeriesData;

    /**
     * ResultSet constructor.
     *
     * @param Meta  $meta
     * @param array $timeSeriesData
     */
    public function __construct(Meta $meta, $timeSeriesData = [])
    {
        $this->meta = $meta;
        $this->timeSeriesData = $timeSeriesData;
    }

    public function getMetaData() : Meta
    {
        return $this->meta;
    }

    public function getTimes() : array
    {
        return array_keys($this->timeSeriesData);
    }

    /**
     * @param string $time
     *
     * @return Technicals
     */
    public function getDataForTime($time) : Technicals
    {
        return $this->timeSeriesData[$time];
    }
}