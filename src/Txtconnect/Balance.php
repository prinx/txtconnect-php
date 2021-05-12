<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\BalanceAbstract;
use Prinx\Txtconnect\Lib\Endpoint;

class Balance extends BalanceAbstract
{
    protected $rawResponse;
    protected $payload = [];
    protected $fetched = false;

    public function fetch()
    {
        $response = $this->request(self::endpoint());

        $this->rawResponse = $response->getContent(false);
        $this->payload = $response->toArray();
        $this->fetched = true;

        return $this;
    }

    public static function endpoint()
    {
        return Endpoint::balance();
    }

    public function value()
    {
        return $this->getOrRefresh('balance');
    }

    public function user()
    {
        return $this->getOrRefresh('user');
    }

    public function country()
    {
        return $this->getOrRefresh('user');
    }

    public function getOrRefresh($key = '')
    {
        if (empty($this->payload) || !$this->false) {
            $this->fetch();
        }

        return $key ? $this->payload[$key] : $this->payload;
    }

    /**
     * Re-fetch balance the next time the user tries to get the balance value.
     *
     * @return $this
     */
    public function refresh()
    {
        $this->fetched = false;

        return $this;
    }

    /**
     * Raw response.
     *
     * @return string|null
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }
}
