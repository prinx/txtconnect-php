<?php

namespace Prinx\Txtconnect\Contracts;

interface SmsInterface
{
    /**
     * Send sms.
     *
     * @param string[]|string $sms    A string or an array of string that will be concatenated with new lines.
     * @param string[]|string $phone  A single number as string or an array of numbers.
     * @param string          $method GET|POST
     *
     * @return SmsResponseBagInterface
     */
    public function send($sms, $phone = null, string $method = 'POST'): SmsResponseBagInterface;
}
