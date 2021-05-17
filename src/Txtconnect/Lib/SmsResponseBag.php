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
     * @var SmsResponse[]
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
     * @var string[]
     */
    protected $originalNumbers;

    /**
     * Parsed numbers.
     *
     * @var string[]
     */
    protected $parsedNumbers;

    /**
     * Number of SmsResponse in the bag.
     *
     * @var int
     */
    protected $count;

    /**
     * Number of SMS truly sent to TXTCONNECT.
     *
     * @var int
     */
    protected $trulySentCount;

    /**
     * @var \Prinx\Txtconnect\SmsResponse
     */
    protected $first;

    /**
     * @var \Prinx\Txtconnect\SmsResponse
     */
    protected $last;

    /**
     * @param bool          $isBeingProcessed Has SMS reached TxtConnect?
     * @param SmsResponse[] $responses        Responses and info on the request
     * @param string[]      $originalNumbers  Original numbers
     * @param string[]      $parsedNumbers    Parsed numbers
     * @param string|null   $error            Error
     */
    public function __construct(bool $isBeingProcessed, array $responses, array $originalNumbers, array $parsedNumbers, int $trulySentCount, $error = null)
    {
        $this->isBeingProcessed = $isBeingProcessed;
        $this->responses = $responses;
        $this->originalNumbers = $originalNumbers;var_dump($originalNumbers);
        $this->parsedNumbers = $parsedNumbers;
        $this->trulySentCount = $trulySentCount;
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

        if (!isset($this->originalNumbers[0])) {
            throw new SmsResponseNotFoundException();
        }

        return $this->first = $this->responses[0];
    }

    /**
     * {@inheritdoc}
     */
    public function last()
    {
        if (!is_null($this->last)) {
            return $this->last;
        }

        if (!isset($this->originalNumbers[0])) {
            throw new SmsResponseNotFoundException();
        }

        return $this->last = $this->responses[$this->count() - 1];
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $number)
    {
        $index = array_search($number, $this->originalNumbers, true);

        if ($index === false) {
            throw new SmsResponseNotFoundException($number);
        }

        return $this->responses[$index];
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
    public function originalNumbers()
    {
        return $this->originalNumbers;
    }

    /**
     * {@inheritdoc}
     */
    public function parsedNumbers()
    {
        return $this->parsedNumbers;
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
            $this->count = count($this->originalNumbers);
        }

        return $this->count;
    }

    /**
     * {@inheritdoc}
     */
    public function trulySentCount()
    {
        return $this->trulySentCount;
    }
}
