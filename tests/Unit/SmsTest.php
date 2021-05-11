<?php

namespace Tests\Unit;

use Prinx\Txtconnect\ResponseCode;
use Prinx\Txtconnect\Sms;
use Tests\TestCase;
use VCR\VCR;

class SmsTest extends TestCase
{
    /**
     * @vcr send-successful-sms.json
     */
    public function testCanSendSuccessfullySms(): void
    {
        $sms = new Sms();

        $message = 'Hi';
        $phone = '233(0) 54 54-66-796';

        $response = $sms->country('GH')->send($message, $phone);
        var_dump($response);
        $this->assertTrue($response->isBeingProcessed());
        $this->assertTrue($response->first()->isOk());
        $this->assertTrue($response->last()->isOk());
        $this->assertTrue($response->get($phone)->isOk());
        $this->assertEquals('233545466796', $response->first()->getNumber(), $response->first()->getNumber().' ');
        $this->assertEquals('233545466796', $response->last()->getNumber());
        $this->assertEquals('233545466796', $response->get($phone)->getNumber());
        $this->assertEquals([$phone => '233545466796'], $response->numbers());
        $this->assertEquals([$phone], $response->originalNumbers());
        $this->assertNull($response->getError());

        $this->assertEquals($response->first()->getCode(), ResponseCode::OK);
        $this->assertIsString($response->first()->getBatchNumber());
        $this->assertIsString($response->first()->getUserName());
        $checkUrl = $response->first()->getStatusCheckUrl();
        $this->assertIsString($checkUrl);
        $this->assertNull($response->first()->getError());
        $this->assertEquals('233545466796', $response->first()->getNumber());
        $this->assertEquals($phone, $response->first()->getOriginalNumber());
        $this->assertIsString($response->first()->getRawResponse());
    }
}
