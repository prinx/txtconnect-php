<?php

namespace Prinx\Txtconnect;

class Endpoint
{
    public static function base($action = '')
    {
        $base = env('TXTCONNECT_ENDPOINT', 'https://txtconnect.net/sms/api');

        return $action ? $base.'?action='.$action : $base;
    }

    public static function balance()
    {
        return static::base('check-balance');
    }

    public static function sms()
    {
        return static::base('send-sms');
    }

    public static function status()
    {
        return static::base('get-status');
    }

    public static function inbox()
    {
        return static::base('get-inbox');
    }
}
