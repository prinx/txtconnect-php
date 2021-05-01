<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Sms;
use Tests\TestCase;

class SmsTest extends TestCase
{
    public function testCanSendSuccessfullySms()
    {
        $sms = new Sms();

        $message = 'Hi';
        $phone = '233545466796';

        $response = $sms->country('GH')->send($message, $phone);

        var_dump($response->first()->getRawResponse());
        $this->assertTrue($response->isBeingProcessed());
        $this->assertTrue($response->first()->isOk());
        $this->assertTrue($response->last()->isOk());
        $this->assertTrue($response->get('233545466796')->isOk());
        $this->assertEquals($response->first()->getNumber(), $phone);
        $this->assertEquals($response->last()->getNumber(), $phone);
        $this->assertEquals($response->get('233545466796')->getNumber(), $phone);
    }
}
