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
     * @var \Prinx\Txtconnect\SmsResponse|false|null
     */
    protected $first = false;

    /**
     * @var \Prinx\Txtconnect\SmsResponse|false|null
     */
    protected $last = false;

    public function __construct(array $responses)
    {
        $this->numbers = $responses['numbers'];
        $this->originalNumbers = $responses['originalNumbers'];
        $this->responses = $responses;

        if (!$responses['success']) {
            $this->error = $responses['error'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isBeingProcessed()
    {
        return $this->responses['success'];
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
        $length = count($this->originalNumbers);

        do {
            $first = $this->get($this->originalNumbers[$index]);
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
        $index = count($this->originalNumbers) - 1;

        do {
            $last = $this->get($this->originalNumbers[$index]);
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
        return $this->responses['data'][$number] ?? null;
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
        return $this->numbers;
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
    public function getError()
    {
        return $this->error;
    }
}
