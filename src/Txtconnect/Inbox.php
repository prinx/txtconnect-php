<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\InboxAbstract;

class Inbox extends InboxAbstract
{
    /**
     * Inbox items in object form.
     *
     * @var array[]|null
     */
    protected $raw = null;

    /**
     * Inbox items in object form.
     *
     * @var InboxItem[]
     */
    protected $items = [];

    protected $fetched = false;

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
        $this->fetched = true;

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

    /**
     * All the items in the inbox.
     *
     * @return InboxItem[]
     */
    public function all()
    {
        $allRaw = $this->nth();

        foreach ($allRaw as $index => $item) {
            if (!isset($this->items[$index])) {
                $this->items[$index] = new InboxItem($item, $index);
            }
        }

        return $this->items;
    }

    /**
     * Get first inbox item.
     *
     * @return InboxItem
     */
    public function first()
    {
        return $this->nth(1);
    }

    /**
     * Get last inbox item.
     *
     * @return InboxItem
     */
    public function last()
    {
        return $this->nth($this->count() - 1);
    }

    public function nth($number = '', $key = '')
    {
        if (is_null($this->raw) || !$this->fetched) {
            $this->fetch();
        }

        if (!$number) {
            return $this->payload;
        }

        if ($key) {
            return $this->raw[$number][$key];
        }

        if (!isset($this->items[$number])) {
            $this->items[$number] = new InboxItem($this->raw[$number], $number);
        }

        return $this->items[$number];
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
