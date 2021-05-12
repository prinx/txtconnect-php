<?php

namespace Prinx\Txtconnect\Exceptions;

class NoSmsSentException extends \RuntimeException
{
    public function __construct($message = '', $code = 0)
    {
        $message = $message ?: 'No SMS has been sent';
        parent::__construct($message, $code);
    }
}
