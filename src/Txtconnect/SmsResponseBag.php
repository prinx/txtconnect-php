<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\SmsResponseBagAbstract;
use Prinx\Txtconnect\Traits\ResponseBagCallback;

class SmsResponseBag extends SmsResponseBagAbstract
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

    /**
     * @var SmsResponse|false|null
     */
    protected $first = false;

    /**
     * @var SmsResponse|false|null
     */
    protected $last = false;

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
     * First Sms processed response.
     *
     * @return SmsResponse|null
     */
    public function first()
    {
        if ($this->first !== false) {
            return $this->first;
        }

        if (!$this->isBeingProcessed()) {
            $this->first = null;

            return null;
        }

        $first = null;
        $index = 0;
        $length = count($this->originalNumbers);

        do {
            $first = $this->get($this->originalNumbers[$index]);
            ++$index;
        } while (is_null($first) && $index < $length);

        $this->first = $first;

        return $first;
    }

    /**
     * Last Sms processed response.
     *
     * @return SmsResponse|null
     */
    public function last()
    {
        if ($this->last !== false) {
            return $this->last;
        }

        if (!$this->isBeingProcessed()) {
            $this->last = null;

            return null;
        }

        $last = null;
        $index = count($this->originalNumbers) - 1;

        do {
            $last = $this->get($this->originalNumbers[$index]);
            --$index;
        } while (is_null($last) && $index >= 0);

        $this->last = $last;

        return $last;
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
