<?php

namespace Prinx\Txtconnect\Abstracts;

use Prinx\Txtconnect\Exceptions\UndefinedSmsMessageContentException;
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
        $allRaw = $this->nth();

        foreach ($allRaw as $index => $item) {
            if (!isset($this->items[$index])) {
                $this->items[$index] = new SmsMessage($item, $index);
            }
        }

        return $this->items;
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
     * Returns the whole SmsBag if index is null.
     *
     * @param int|null $index Index of the SmsMessage to return.
     * @param string   $key   Key of the SmsMessage to return. If Not passed the whole nth SmsMessage is return
     *
     * @return SmsMessage[]|SmsMessage|array|mixed
     */
    public function nth($index = null, $key = '')
    {
        if (is_null($this->raw)) {
            $this->fetch();
        }

        if (is_null($index)) {
            return $this->raw;
        }

        if (!isset($this->raw[$index])) {
            throw new UndefinedSmsMessageException($index);
        }

        if (!$key) {
            if (!isset($this->items[$index])) {
                $this->items[$index] = new SmsMessage($this->raw[$index], $index);
            }

            return $this->items[$index];
        }

        if (!isset($this->raw[$index][$key])) {
            throw new UndefinedSmsMessageContentException($index, $key);
        }

        return $this->items[$index][$key];
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
        return count($this->nth());
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
