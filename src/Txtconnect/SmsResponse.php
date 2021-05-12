<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\SmsResponseAbstract;

class SmsResponse extends SmsResponseAbstract
{
    protected $rawResponse;
    protected $response;
    protected $error = null;
    protected $parsedNumber = null;
    protected $originalNumber = null;
    protected $isOk = null;

    const OK = 'ok';
    const ERROR = 'error';

    /**
     * Create new instance of SmsResponse.
     *
     * @param string|\Symfony\Contracts\HttpClient\ResponseInterface $response
     * @param string                                                 $originalNumber
     * @param string                                                 $parsedNumber
     *
     * @return $this
     */
    public function __construct($response, $originalNumber, $parsedNumber)
    {
        $this->rawResponse = is_string($response) ? $response : $response->getContent(false);
        $this->parsedNumber = $parsedNumber;
        $this->originalNumber = $originalNumber;

        if (is_string($response)) {
            $this->error = $response;

            return;
        }

        $this->response = $response->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function isOk()
    {
        if (is_null($this->isOk)) {
            $this->isOk = strtolower($this->getCode()) === self::OK;
        }

        return $this->isOk;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getResponse('code', self::ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchNumber()
    {
        return $this->getResponse('batch_no', null);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserName()
    {
        return $this->getResponse('user', null);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCheckUrl()
    {
        return $this->getResponse('status_check_url', null);
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance()
    {
        return $this->getResponse('balance', null);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->getResponse('message', $this->error);
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
    public function getParsedNumber()
    {
        return $this->parsedNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalNumber()
    {
        return $this->originalNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse($key = null, $default = null)
    {
        return $key ? $this->response[$key] ?? $default : $this->response;
    }
}
