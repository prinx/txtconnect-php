<?php

namespace Prinx\Txtconnect\Exceptions;

class UndefinedSmsMessageContentException extends \InvalidArgumentException
{
    public function __construct($number, $key)
    {
        parent::__construct('Key '.$key.' does not exist in content of inbox item number '.$number);
    }
}
