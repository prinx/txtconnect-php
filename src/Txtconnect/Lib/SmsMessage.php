<?php

namespace Prinx\Txtconnect\Lib;

use Prinx\Txtconnect\Exceptions\UndefinedSmsMessageContentException;

class SmsMessage
{
    protected $content;

    public function __construct(array $content, int $index)
    {
        $this->content = $content;
        $this->content['index'] = $index;
    }

    /**
     * Has sms been delivered successfully?
     *
     * @return bool
     */
    public function isDelivered()
    {
        return strtolower($this->status()) === 'message delivered successfully';
    }

    /**
     * @return int
     */
    public function id()
    {
        return $this->content('id');
    }

    /**
     * Batch number.
     *
     * @return string
     */
    public function batchNo()
    {
        return $this->content('batch_no');
    }

    /**
     * Sender Id used to send the SMS.
     *
     * @return string
     */
    public function senderId()
    {
        return $this->content('from');
    }

    /**
     * Phone number SMS was sent to.
     *
     * @return string
     */
    public function recipient()
    {
        return $this->content('phone');
    }

    /**
     * Text of the SMS.
     *
     * @return string
     */
    public function text()
    {
        return $this->content('sms');
    }

    /**
     * Amount deducted when sending SMS.
     *
     * @return int
     */
    public function amount()
    {
        return $this->content('amount');
    }

    /**
     * @return int
     */
    public function segments()
    {
        return $this->content('segments');
    }

    /**
     * Status of the SMS.
     *
     * @return string
     */
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

    /**
     * Get SmsMessage content or key of content.
     *
     * @param string $key
     *
     * @throws UndefinedSmsMessageContentException
     *
     * @return array|mixed
     */
    public function content($key = '')
    {
        if ($key && !isset($this->content[$key])) {
            throw new UndefinedSmsMessageContentException($this->index(), $key);
        }

        return $key ? $this->content[$key] : $this->content;
    }
}
