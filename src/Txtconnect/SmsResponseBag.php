<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\SmsResponseBagAbstract;
use Prinx\Txtconnect\Traits\ResponseBagCallback;

class SmsResponseBag extends SmsResponseBagAbstract
{
    use ResponseBagCallback;

    protected $isBeingProcessed = false;

    /**
     * @var array<string,SmsResponse>
     */
    protected $responses = [];

    /**
     * Error.
     *
     * @var string|null
     */
    protected $error = null;

    /**
     * Original numbers.
     *
     * @var array|null
     */
    protected $originalNumbers;

    /**
     * Map of original numbers to parsed numbers.
     *
     * @var array<string,string>
     */
    protected $numberMap = [];

    /**
     * @var \Prinx\Txtconnect\SmsResponse|false|null
     */
    protected $first = false;

    /**
     * @var \Prinx\Txtconnect\SmsResponse|false|null
     */
    protected $last = false;

    /**
     * @param array  $isBeingProcessed Has SMS reached TxtConnect?
     * @param array  $responses        Responses and info on the request
     * @param array  $numberMap        original numbers mapped to parsed numbers
     * @param string $error            original numbers mapped to parsed numbers
     */
    public function __construct(bool $isBeingProcessed, array $responses, array $numberMap, $error = null)
    {
        $this->isBeingProcessed = $isBeingProcessed;
        $this->responses = $responses;
        $this->numberMap = $numberMap;
        $this->error = $error;
    }

    /**
     * {@inheritdoc}
     */
    public function isBeingProcessed()
    {
        return $this->isBeingProcessed;
    }

    /**
     * {@inheritdoc}
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
        $length = count($this->originalNumbers());

        do {
            $first = $this->get($this->originalNumbers()[$index]);
            ++$index;
        } while (is_null($first) && $index < $length);

        $this->first = $first;

        return $first;
    }

    /**
     * {@inheritdoc}
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
        $index = count($this->originalNumbers()) - 1;

        do {
            $last = $this->get($this->originalNumbers()[$index]);
            --$index;
        } while (is_null($last) && $index >= 0);

        $this->last = $last;

        return $last;
    }

    /**
     * {@inheritdoc}
     */
    public function get($number = null)
    {
        if (isset($this->responses[$number])) {
            return $this->responses[$number];
        }

        $originalNumber = array_search($number, $this->numberMap, true);

        if ($originalNumber !== false) {
            return $this->responses[$originalNumber];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * {@inheritdoc}
     */
    public function numbers()
    {
        return $this->numberMap;
    }

    /**
     * {@inheritdoc}
     */
    public function originalNumbers()
    {
        if (is_null($this->originalNumbers)) {
            $this->originalNumbers = array_keys($this->numberMap);
        }

        return $this->originalNumbers;
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        return $this->error;
    }
}
