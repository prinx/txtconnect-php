<?php

namespace Prinx\Txtconnect;

use Prinx\Notify\Log;
use Txtpay\Support\SlackLog;

class Logger extends Log
{
    public function __construct($file = '')
    {
        $this->setFile($file ?: $this->defaultFile());
    }

    public function log(string $level, $message, $flag = FILE_APPEND)
    {
        if (env('TXTCONNECT_LOG_ENABLED', null) === false) {
            return $this;
        }

        SlackLog::log($message, $level, 'TXTCONNECT');

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
