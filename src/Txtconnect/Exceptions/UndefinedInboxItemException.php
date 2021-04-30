<?php

namespace Prinx\Txtconnect\Exceptions;

class UndefinedInboxItemException extends \InvalidArgumentException
{
    public function __construct($number)
    {
        parent::__construct('Item number '.$number.' does not exist in inbox.');
    }
}
