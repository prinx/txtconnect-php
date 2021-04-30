<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\InboxAbstract;

class Inbox extends InboxAbstract
{
    protected $inbox = null;
    protected $fetched = false;

    public function fetch()
    {
        $response = $this->request(self::endpoint());

        $this->inbox = $response->toArray();
        $this->fetched = true;

        return $this;
    }

    public static function endpoint()
    {
        return Endpoint::inbox();
    }

    public function all()
    {
        return $this->nth();
    }

    public function first()
    {
        return $this->nth(1);
    }

    public function last()
    {
        return $this->nth($this->count() - 1);
    }

    public function nth($number = '', $key = '')
    {
        if (is_null($this->inbox) || !$this->fetched) {
            $this->fetch();
        }

        if (!$number) {
            return $this->payload;
        }

        return $key ? $this->inbox[$number][$key] : $this->inbox[$number];
    }

    public function count()
    {
        return count($this->nth());
    }

    public function refresh()
    {
        $this->fetched = false;

        return $this;
    }
}
