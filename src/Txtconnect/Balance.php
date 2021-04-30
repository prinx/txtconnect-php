<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\BalanceAbstract;

class Balance extends BalanceAbstract
{
    protected $payload = [];
    protected $fetched = false;

    public function get()
    {
        $response = $this->request(self::endpoint());

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
            $this->get();
        }

        return $key ? $this->payload[$key] : $this->payload;
    }

    public function refresh()
    {
        $this->fetched = false;

        return $this;
    }
}
