<?php

namespace Prinx\Txtconnect\Exceptions;

class SmsResponseNotFoundException extends \RuntimeException
{
    public function __construct($number = '', $code = 0)
    {
        $message = $number ? 'No SMS response found for number '.$number : 'No SMS response found';
        parent::__construct($message, $code);
    }
}
