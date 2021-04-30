<?php

namespace Prinx\Txtconnect\Abstracts;

use Prinx\Txtconnect\Contracts\ApiInterface;
use Prinx\Txtconnect\Exceptions\InvalidApiKeyException;
use Prinx\Txtconnect\Exceptions\InvalidHttpMethodException;
use Symfony\Component\HttpClient\HttpClient;
use function Prinx\Dotenv\env;

abstract class ApiAbstract implements ApiInterface
{
    const ENV_PREFIX = 'TXTCONNECT';

    protected $envPrefix = self::ENV_PREFIX;

    /**
     * @var HttpClient
     */
    protected $client = null;

    /**
     * @var string
     */
    protected $apiKey = null;

    protected $supportedMethods = ['GET', 'POST'];

    protected $method = 'POST';

    /**
     * Set the HTTP method to use for the API request.
     *
     * @return $this
     *
     * @throws InvalidHttpMethodException
     */
    public function via(string $method)
    {
        $method = strtoupper($method);

        if (!in_array($method, $this->supportedMethods)) {
            throw new InvalidHttpMethodException('Invalid HTTP method.');
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Send request to API.
     *
     * @return \Symfony\Contracts\HttpClient\ResponseInterface
     */
    public function request(string $endpoint, array $options = [])
    {
        $options = $options ?: $this->prepareParams();

        return $this->client()->request($this->method, $endpoint, $options);
    }

    /**
     * HTTP Client.
     *
     * @return \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    public function client()
    {
        if (is_null($this->client)) {
            $this->client = HttpClient::create();
        }

        return $this->client;
    }

    /**
     * Params.
     *
     * @return array
     */
    public function prepareParams()
    {
        return [
            'api_key' => $this->getApiKey(),
            'response' => 'json',
        ];
    }

    /**
     * Get Api Key.
     *
     * @return string
     *
     * @throws InvalidApiKeyException
     */
    public function getApiKey()
    {
        $key = $this->apiKey ?: env($this->envPrefix.'_KEY');

        if (!$key) {
            throw new InvalidApiKeyException('API key not set.');
        }

        return $key;
    }

    public function env(string $prefix)
    {
        $this->envPrefix = $prefix;

        return $this;
    }

    public function withDefaultEnv()
    {
        $this->envPrefix = self::ENV_PREFIX;

        return $this;
    }
}