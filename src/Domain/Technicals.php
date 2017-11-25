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
     * @var null
     */
    private $unadjustedClose;
    /**
     * @var null
     */
    private $adjustedClose;

    /**
     * Technicals constructor.
     *
     * @param float|null $open
     * @param float|null $high
     * @param float|null $low
     * @param float|null $close
     * @param int|null   $volume
     * @param float|null $unadjustedClose
     * @param float|null $adjustedClose
     */
    public function __construct(
        $open = null,
        $high = null,
        $low = null,
        $close = null,
        $volume = null,
        $unadjustedClose = null,
        $adjustedClose = null
    ) {
        $this->open = $open;
        $this->high = $high;
        $this->low = $low;
        $this->close = $close;
        $this->volume = $volume;
        $this->unadjustedClose = $unadjustedClose;
        $this->adjustedClose = $adjustedClose;
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

    /**
     * @return null
     */
    public function getUnadjustedClose()
    {
        return $this->unadjustedClose;
    }

    /**
     * @return null
     */
    public function getAdjustedClose()
    {
        return $this->adjustedClose;
    }
}