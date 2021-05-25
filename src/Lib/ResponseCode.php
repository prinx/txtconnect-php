<?php

namespace Prinx\Txtconnect\Lib;

class ResponseCode
{
    const OK = 'ok';
    const BAD_GATEWAY = '100';
    const WRONG_ACTION = '101';
    const AUTH_FAILED = '102';
    const INVALID_PHONE_NUMBER = '103';
    const PHONE_REGION_NOT_COVERED = '104';
    const INSUFFICIENT_BALANCE = '105';
    const INVALID_SENDER_NAME = '106';
    const INVALID_SMS_TYPE = '107';
    const GATEWAY_INACTIVE = '108';
    const INVALID_SCHEDULE_TIME = '109';
    const MEDIA_URL_REQUIRED = '110';
    const SPAM_WAIT_APPROVAL = '111';

    protected static $messages = [
        self::OK                       => 'Message received for delivery.',
        self::BAD_GATEWAY              => 'Bad gateway requested.',
        self::WRONG_ACTION             => 'Wrong action.',
        self::AUTH_FAILED              => 'Authentication failed',
        self::INVALID_PHONE_NUMBER     => 'Invalid Phone Number.',
        self::PHONE_REGION_NOT_COVERED => 'Phone coverage not active.',
        self::INSUFFICIENT_BALANCE     => 'Insufficient balance.',
        self::INVALID_SENDER_NAME      => 'Invalid Sender ID.',
        self::INVALID_SMS_TYPE         => 'Invalid SMS Type.',
        self::GATEWAY_INACTIVE         => 'SMS Gateway not active.',
        self::INVALID_SCHEDULE_TIME    => 'Invalid Schedule Time.',
        self::MEDIA_URL_REQUIRED       => 'Media url required.',
        self::SPAM_WAIT_APPROVAL       => 'SMS contain spam word. Wait for approval.',
    ];

    public static function message($code, $throw = false)
    {
        if (!isset(self::$messages[$code]) && $throw) {
            throw new \InvalidArgumentException('Invalid code. The code provided does not seem to be a Txtconnect API response code.');
        }

        return self::$messages[$code] ?? '';
    }

    /**
     * All responses codes.
     *
     * @return string[]
     */
    public static function codes()
    {
        return array_keys(self::$messages);
    }
}
