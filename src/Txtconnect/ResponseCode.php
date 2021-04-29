<?php

namespace Prinx\Txtconnect;

class ResponseCode
{
    const OK = 'ok';
    const BAD_GATEWAY = '100';
    const INVALID_PHONE_NUMBER = '103';

    protected static $messages = [
        self::OK => 'Message received for delivery.',
        self::BAD_GATEWAY => 'Bad gateway requested',
        self::INVALID_PHONE_NUMBER => 'Invalid Phone Number',
    ];

    public static function message($code, $throw = false)
    {
        if (!isset(self::$messages[$code]) && $throw) {
            throw new \InvalidArgumentException('Invalid code. The code provided does not seem to be a Txtconnect API response code.');
        }

        return self::$messages[$code] ?? '';
    }
}
