<?php

namespace Prinx\Txtconnect;

class SmsMessage
{
    protected $content;

    public function __construct(array $content, int $index)
    {
        $this->content = $content;
        $this->content['index'] = $index;
    }

    public function id()
    {
        return $this->content('id');
    }

    public function batchNo()
    {
        return $this->content('batch_no');
    }

    public function senderId()
    {
        return $this->content('from');
    }

    public function recipient()
    {
        return $this->content('phone');
    }

    public function text()
    {
        return $this->content('sms');
    }

    public function amount()
    {
        return $this->content('amount');
    }

    public function segments()
    {
        return $this->content('segments');
    }

    public function status()
    {
        return $this->content('status');
    }

    public function type()
    {
        return $this->content('type');
    }

    /**
     * The index of this item in the inbox.
     *
     * @return int
     */
    public function index()
    {
        return $this->content('index');
    }

    public function content($key = '')
    {
        if (!isset($this->content[$key])) {
            throw new \InvalidArgumentException('Key '.$key.' not defined on InboxItem content.');
        }

        return $key ? $this->content[$key] : $this->content;
    }
}
