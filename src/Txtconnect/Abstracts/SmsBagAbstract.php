<?php

namespace Prinx\Txtconnect\Abstracts;

use Prinx\Txtconnect\Exceptions\UndefinedSmsMessageException;
use Prinx\Txtconnect\Lib\SmsMessage;

abstract class SmsBagAbstract extends ApiAbstract
{
    /**
     * Inbox items in array form.
     *
     * @var array[]|null
     */
    protected $raw = null;

    /**
     * Inbox items in object form.
     *
     * @var SmsMessage[]
     */
    protected $items = [];

    /**
     * Fetch the items.
     *
     * @return $this
     */
    abstract public function fetch();

    /**
     * All the items in the inbox.
     *
     * @return SmsMessage[]
     */
    public function all()
    {
        foreach ($this->raw as $index => $item) {
            if (!isset($this->items[$index])) {
                $this->items[$index] = new SmsMessage($item, $index);
            }
        }

        return $this->items;
    }

    /**
     * Return all items of the bag as arrays.
     *
     * @return array[]
     */
    public function toArray()
    {
        return $this->raw;
    }

    /**
     * Get first inbox item.
     *
     * @return SmsMessage
     */
    public function first()
    {
        return $this->nth(0);
    }

    /**
     * Get last inbox item.
     *
     * @return SmsMessage
     */
    public function last()
    {
        return $this->nth($this->count() - 1);
    }

    /**
     * Get nth message item.
     *
     * @param int $index Index of the SmsMessage to return.
     *
     * @return SmsMessage
     *
     * @throws UndefinedSmsMessageException If index not in the bag.
     */
    public function nth(int $index)
    {
        if (is_null($this->raw)) {
            $this->fetch();
        }

        if (!isset($this->raw[$index])) {
            throw new UndefinedSmsMessageException($index);
        }

        if (!isset($this->items[$index])) {
            $this->items[$index] = new SmsMessage($this->raw[$index], $index);
        }

        return $this->items[$index];
    }

    public function refresh()
    {
        $this->raw = null;

        return $this;
    }

    /**
     * The number of items in the bag.
     *
     * @return int
     */
    public function count()
    {
        return count($this->raw);
    }

    public function isEmpty()
    {
        return $this->count() === 0;
    }

    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }
}
