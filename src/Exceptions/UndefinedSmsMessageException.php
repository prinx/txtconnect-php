<?php

namespace Prinx\Txtconnect\Exceptions;

class UndefinedSmsMessageException extends \InvalidArgumentException
{
    public function __construct($number)
    {
        parent::__construct('Item '.$number.' does not exist in inbox.');
    }
}
