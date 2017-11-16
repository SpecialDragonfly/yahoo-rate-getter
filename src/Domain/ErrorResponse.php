<?php
namespace RateGetter\Domain;

class ErrorResponse implements RateResponse
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * ErrorResponse constructor.
     *
     * @param string $message
     * @param int    $statusCode
     */
    public function __construct(string $message, int $statusCode)
    {
        $this->message = $message;
        $this->code = $statusCode;
    }

    /**
     * @return string
     */
    public function getShortCode() : string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }
}