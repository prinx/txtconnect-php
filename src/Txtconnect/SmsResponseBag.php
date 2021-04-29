<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Contracts\SmsResponseBagInterface;
use ResponseBagCallback;

class SmsResponseBag implements SmsResponseBagInterface
{
    use ResponseBagCallback;

    /**
     * @var array
     */
    protected $responses = [];

    /**
     * Error.
     *
     * @var string|null
     */
    protected $error = null;

    /**
     * @var array
     */
    protected $numbers = [];

    /**
     * @var array
     */
    protected $originalNumbers = [];

    public function __construct(array $responses)
    {
        $this->numbers = $responses['numbers'];
        $this->originalNumbers = $responses['originalNumbers'];

        if (!$responses['success']) {
            $this->error = $responses['error'];

            return;
        }

        $this->responses = $responses;
    }

    /**
     * Get response for specified number.
     *
     * @param string|null $number
     *
     * @return SmsResponse
     */
    public function get($number = null)
    {
        return $this->responses['data'][$number] ?? null;
    }

    /**
     * Responses.
     *
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Have TXTCONNECT successfully received SMSes for processing?
     *
     * @return boolean
     */
    public function isBeingProcessed()
    {
        return $this->responses['success'];
    }

    /**
     * Actual numbers SMS was sent to. This might be different from the
     * original numbers because the numbers are sanitize before SMS is sent.
     *
     * @return array
     */
    public function numbers()
    {
        return $this->numbers;
    }

    /**
     * Original numbers the developer sent the SMS to.
     *
     * @return array
     */
    public function originalNumbers()
    {
        return $this->originalNumbers;
    }

    /**
     * Error if request is not being processed.
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }
}
