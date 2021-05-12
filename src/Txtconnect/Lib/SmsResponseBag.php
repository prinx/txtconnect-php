<?php

namespace Prinx\Txtconnect\Lib;

use Prinx\Txtconnect\Abstracts\SmsResponseBagAbstract;
use Prinx\Txtconnect\Exceptions\SmsResponseNotFoundException;
use Prinx\Txtconnect\Traits\ResponseBagCallback;

class SmsResponseBag extends SmsResponseBagAbstract
{
    use ResponseBagCallback;

    /**
     * Has SMS reached TxtConnect?
     *
     * @var bool
     */
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
     * @var array
     */
    protected $originalNumbers;

    /**
     * Map of original numbers to parsed numbers.
     *
     * @var array<string,string>
     */
    protected $numberMap = [];

    /**
     * Number of SmsResponse in the bag.
     *
     * @var int
     */
    protected $count;

    /**
     * @var \Prinx\Txtconnect\SmsResponse
     */
    protected $first;

    /**
     * @var \Prinx\Txtconnect\SmsResponse
     */
    protected $last;

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
        if (!is_null($this->first)) {
            return $this->first;
        }

        $numbers = $this->originalNumbers();

        if (!isset($numbers[0])) {
            throw new SmsResponseNotFoundException();
        }

        return $this->first = $this->get($numbers[0]);
    }

    /**
     * {@inheritdoc}
     */
    public function last()
    {
        if (!is_null($this->last)) {
            return $this->last;
        }

        $count = $this->count();

        if (!$count) {
            throw new SmsResponseNotFoundException();
        }

        $number = $this->originalNumbers()[$count - 1];

        return $this->last = $this->get($number);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $number)
    {
        if (isset($this->responses[$number])) {
            return $this->responses[$number];
        }

        $originalNumber = array_search($number, $this->numberMap, true);

        if ($originalNumber !== false) {
            return $this->responses[$originalNumber];
        }

        throw new SmsResponseNotFoundException($number);
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

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (is_null($this->count)) {
            $this->count = count($this->numberMap);
        }

        return $this->count;
    }
}