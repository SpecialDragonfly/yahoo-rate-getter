<?php
namespace RateGetter\Domain;

class Meta
{
    /**
     * @var array
     */
    private $meta;

    /**
     * Meta constructor.
     *
     * @param array $meta
     */
    public function __construct(array $meta = [])
    {
        $this->meta = $meta;
    }

    /**
     * @return string
     */
    public function getCurrency() : string {
        return $this->meta['currency'];
    }

    /**
     * @return string
     */
    public function getSymbol() : string {
        return $this->meta['symbol'];
    }

    /**
     * @return string
     */
    public function getExchange() : string {
        return $this->meta['exchangeName'];
    }

    /**
     * @return string
     */
    public function getInstrumentType() : string {
        return $this->meta['instrumentType'];
    }

    /**
     * @return string
     */
    public function getDataGranularity() : string {
        return $this->meta['dataGranularity'];
    }
}