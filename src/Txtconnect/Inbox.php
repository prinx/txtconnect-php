<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\InboxAbstract;

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

    public function refresh()
    {
        $this->raw = null;

        return $this;
    }
}
