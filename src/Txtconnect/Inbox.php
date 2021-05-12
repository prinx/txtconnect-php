<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\InboxAbstract;
use Prinx\Txtconnect\Lib\Endpoint;

class Inbox extends InboxAbstract
{
    /**
     * Get inbox content fron API.
     *
     * @return $this
     */
    public function fetch()
    {
        $response = $this->request(self::endpoint());

        $this->raw = $response->toArray();
        $this->items = [];

        return $this;
    }

    /**
     * Get Inbox endpoint.
     *
     * @return string
     */
    public static function endpoint()
    {
        return Endpoint::inbox();
    }
}
