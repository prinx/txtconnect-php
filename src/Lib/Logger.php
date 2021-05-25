<?php

namespace Prinx\Txtconnect\Lib;

use Prinx\Notify\Log;
use Txtpay\Support\SlackLog;

class Logger extends Log
{
    public function __construct($file = '')
    {
        parent::__construct($file ?: $this->defaultFile());
    }

    public function log(string $level, $message, $flag = FILE_APPEND)
    {
        if (env('TXTCONNECT_LOG_ENABLED', null) === false) {
            return $this;
        }

        SlackLog::log($message, $level, 'TXTCONNECT_SLACK');

        if (env('TXTCONNECT_LOCAL_LOG_ENABLED', true) === false) {
            return $this;
        }

        parent::log($level, $message, $flag);

        return $this;
    }

    public function defaultFile(): string
    {
        return realpath(__DIR__.'/../../../').'/storage/logs/txtconnect/sms.log';
    }
}
