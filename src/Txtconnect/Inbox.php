<?php

namespace Prinx\Txtconnect;

use Prinx\Txtconnect\Abstracts\InboxAbstract;
use Prinx\Txtconnect\Lib\Endpoint;
use Prinx\Txtconnect\Lib\PhoneNumber;
use libphonenumber\NumberParseException;

class Inbox extends InboxAbstract
{
    protected $keyByPhoneBag = [];

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

    public function formatPhoneNumber($number)
    {
        
    }

    /**
     * Get an array of all SMS sent to the phone number.
     *
     * @return array
     */
    public function get(string $number)
    {
        $number = PhoneNumber::sanitize($number);

        if (!isset($this->keyByPhoneBag[$number])) {
            return $this->keyByPhoneBag[$number];
        }

        $this->keyByPhoneBag[$number] = [];

        foreach ($this->all() as $sms) {
            if ($sms->recipient() === $number) {
                $this->keyByPhoneBag[$number][] = $sms;
            }
        }

        return $this->keyByPhoneBag[$number];
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
