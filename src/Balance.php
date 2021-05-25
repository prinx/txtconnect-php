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

    public function amount()
    {
        return $this->getOrRefresh('balance');
    }

    public function user()
    {
        return $this->getOrRefresh('user');
    }

    public function country()
    {
        return $this->getOrRefresh('country');
    }

    public function getOrRefresh($key = '')
    {
        if (empty($this->payload) || !$this->fetched) {
            $this->fetch();
        }

        return $key ? $this->payload[$key] : $this->payload;
    }

    public function refresh()
    {
        $this->fetched = false;

        return $this;
    }

    public function getRawResponse()
    {
        return $this->rawResponse;
    }
}
