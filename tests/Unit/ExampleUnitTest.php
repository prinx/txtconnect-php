<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Sms;
use Tests\TestCase;

class ExampleUnitTest extends TestCase
{
    public function testExample()
    {
        $sms = new Sms();

        $message = 'Hi';
        $phone = '233545466796';
        $response = $sms->send($message, $phone);
    }
}
