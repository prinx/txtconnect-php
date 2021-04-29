<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\SmsResponseAbstract;

class SmsResponse extends SmsResponseAbstract
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
        $this->rawResponse = is_string($response) ? $response : $response->getContent(false);
        $this->number = $number;
        $this->originalNumber = $originalNumber;

        if (is_string($response)) {
            $this->error = $response;

            return;
        }

        $this->response = $response->toArray();
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
        return $this->getResponse('code', self::ERROR);
    }

    public function getBatchNumber()
    {
        return $this->getResponse('batch_no', null);
    }

    public function getUserName()
    {
        return $this->getResponse('user', null);
    }

    public function getStatusCheckUrl()
    {
        return $this->getResponse('status_check_url', null);
    }

    public function getBalance()
    {
        return $this->getResponse('balance', null);
    }

    public function getMessage()
    {
        return $this->getResponse('message', $this->error);
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

    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    public function getResponse($key = null, $default = null)
    {
        return $key ? $this->response[$key] ?? $default : $this->response;
    }
}
