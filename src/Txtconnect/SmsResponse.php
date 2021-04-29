<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Contracts\SmsResponseInterface;

class SmsResponse implements SmsResponseInterface
{
    protected $rawResponse;
    protected $response;
    protected $error = null;
    protected $number = null;
    protected $originalNumber = null;
    protected $isOk = null;

    const OK = 'ok';
    const ERROR = 'error';

    /**
     * Create new instance of SmsResponse.
     *
     * @param string|\Symfony\Contracts\HttpClient\ResponseInterface $response
     * @param string                                                 $number
     * @param string                                                 $originalNumber
     *
     * @return $this;
     */
    public function __construct($response, $number, $originalNumber)
    {
        $this->rawResponse = $response;
        $this->number = $number;
        $this->originalNumber = $originalNumber;

        if (is_string($response)) {
            $this->error = $response;

            return;
        }

        $this->response = $response['request_response']->getContent();
    }

    public function isOk()
    {
        if (is_null($this->isOk)) {
            $this->isOk = strtolower($this->getCode()) === self::OK;
        }

        return $this->isOk;
    }

    public function getCode()
    {
        return $this->response['code'] ?? self::ERROR;
    }

    public function getBatchNumber()
    {
        return $this->response['batch_no'] ?? null;
    }

    public function getUserName()
    {
        return $this->response['user'] ?? null;
    }

    public function getStatusCheckUrl()
    {
        return $this->response['status_check_url'] ?? null;
    }

    public function getBalance()
    {
        return $this->response['balance'] ?? null;
    }

    public function getMessage()
    {
        return $this->response['message'] ?? $this->error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getOriginalNumber()
    {
        return $this->originalNumber;
    }
}
