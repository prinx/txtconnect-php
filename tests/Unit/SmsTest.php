<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Sms;
use Tests\TestCase;
use VCR\VCR;

class SmsTest extends TestCase
{
    protected function setUp(): void
    {
        VCR::configure()
            ->setStorage('json')
            ->setBlackList([
                'vendor/prinx',
                'vendor/nunomaduro',
                'vendor/phpunit',
                'vendor/giggsey',
            ]);
    }

    public function testCanSendSuccessfullySms(): void
    {
        $sms = new Sms();

        $message = 'Hi';
        $phone = '233545466796';

        VCR::turnOn();
        VCR::insertCassette('send-successful-sms.json');
        $response = $sms->country('GH')->send($message, $phone);
        // VCR::eject();
        VCR::turnOff();

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
