<?php
namespace RateGetter\Domain;

class Technicals
{
    private $open;
    private $high;
    private $low;
    private $close;
    private $volume;

    /**
     * Technicals constructor.
     *
     * @param float|null $open
     * @param float|null $high
     * @param float|null $low
     * @param float|null $close
     * @param int|null $volume
     */
    public function __construct(
        $open = null,
        $high = null,
        $low = null,
        $close = null,
        $volume = null
    ) {
        $this->open = $open;
        $this->high = $high;
        $this->low = $low;
        $this->close = $close;
        $this->volume = $volume;
    }

    /**
     * @return float|null
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * @return float|null
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * @return float|null
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * @return float|null
     */
    public function getClose()
    {
        return $this->close;
    }

    /**
     * @return int|null
     */
    public function getVolume()
    {
        return $this->volume;
    }
}