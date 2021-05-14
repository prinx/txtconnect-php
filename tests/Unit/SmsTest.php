<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Lib\ResponseCode;
use Prinx\Txtconnect\Sms;
use Prinx\Txtconnect\SmsStatus;
use Tests\TestCase;

class SmsTest extends TestCase
{
    public function setUp(): void
    {
        $this->message = 'Hi';
        $this->originalNumber = '233(0) 54 54-66-796';
        $this->parsedNumber = '233545466796';

        $this->response1 = (new Sms())->country('GH')->send($this->message, $this->originalNumber);
        $this->response2 = (new Sms())->country('GH')->send($this->message, $this->originalNumber);
    }

    /**
     * @vcr send-successful-sms.json
     */
    public function testCanSendSuccessfullySms()
    {
        $this->assertTrue($this->response1->isBeingProcessed());
        $this->assertTrue($this->response2->isBeingProcessed());
        $this->assertTrue($this->response3->isBeingProcessed());
    }

    public function testIsOk()
    {
        $this->assertTrue($this->response1->first()->isOk());
        $this->assertTrue($this->response1->last()->isOk());

        $this->assertTrue($this->response1->get($this->originalNumber)->isOk());
        $this->assertTrue($this->response1->get($this->originalNumber)->isOk());

        $this->assertTrue($this->response1->get($this->parsedNumber)->isOk());
        $this->assertTrue($this->response1->get($this->parsedNumber)->isOk());
    }

    public function testGettingProperFirst()
    {
        $this->assertSame($this->response1->first(), $this->response1->get($this->originalNumber));
        $this->assertSame($this->response1->first(), $this->response1->get($this->parsedNumber));
    }

    public function testGettingProperLast()
    {
        $this->assertSame($this->response1->last(), $this->response1->get($this->originalNumber));
        $this->assertSame($this->response1->last(), $this->response1->get($this->parsedNumber));
    }

    public function testResolvingNumbersWell()
    {
        $this->assertEquals([$this->originalNumber => $this->parsedNumber], $this->response1->numbers());
        $this->assertEquals([$this->originalNumber], $this->response1->originalNumbers());

        $this->assertEquals($this->parsedNumber, $this->response1->first()->getParsedNumber());
        $this->assertEquals($this->parsedNumber, $this->response1->last()->getParsedNumber());

        $this->assertEquals($this->originalNumber, $this->response1->first()->getOriginalNumber());
        $this->assertEquals($this->originalNumber, $this->response1->last()->getOriginalNumber());

        $this->assertEquals($this->parsedNumber, $this->response1->get($this->originalNumber)->getParsedNumber());
        $this->assertEquals($this->parsedNumber, $this->response1->get($this->originalNumber)->getParsedNumber());

        $this->assertEquals($this->parsedNumber, $this->response1->get($this->parsedNumber)->getParsedNumber());
        $this->assertEquals($this->parsedNumber, $this->response1->get($this->parsedNumber)->getParsedNumber());

        $this->assertEquals($this->originalNumber, $this->response1->get($this->originalNumber)->getOriginalNumber());
        $this->assertEquals($this->originalNumber, $this->response1->get($this->originalNumber)->getOriginalNumber());

        $this->assertEquals($this->originalNumber, $this->response1->get($this->parsedNumber)->getOriginalNumber());
        $this->assertEquals($this->originalNumber, $this->response1->get($this->parsedNumber)->getOriginalNumber());
    }

    public function testGettingRightError()
    {
        $this->assertNull($this->response1->getError());
        $this->assertNull($this->response1->first()->getError());
    }

    public function testGettingRightCode()
    {
        $this->assertEquals($this->response1->first()->getCode(), ResponseCode::OK);
    }

    public function testUsernameIsString()
    {
        $this->assertIsString($this->response1->first()->getUserName());
    }

    public function testStatusCheckUrlIsString()
    {
        $this->assertIsString($this->response1->first()->getStatusCheckUrl());
    }

    public function testRawResponseIsString()
    {
        $this->assertIsString($this->response1->first()->getRawResponse());
    }

    public function testBatchNumberIsString()
    {
        $this->assertIsString($this->response1->first()->getBatchNumber());
    }

    public function testGetOneSmsStatusWithGet()
    {
        $status = (new SmsStatus())->of($this->response1->first()->getBatchNumber())->get();
        $this->assertEquals($this->response1->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals($this->response1->first()->getMessage(), $status->text());
        $this->assertContains($this->response1->first()->getCode(), ResponseCode::codes());
    }

    public function testGetOneSmsStatusWithFirst()
    {
        $status = (new SmsStatus())->of($this->response1->first()->getBatchNumber())->first();
        $this->assertEquals($this->response1->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals($this->response1->first()->getMessage(), $status->text());
        $this->assertContains($this->response1->first()->getCode(), ResponseCode::codes());
    }

    public function testGetOneSmsStatusWithLast()
    {
        $status = (new SmsStatus())->of($this->response1->first()->getBatchNumber())->last();
        $this->assertEquals($this->response1->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals($this->response1->first()->getMessage(), $status->text());
        $this->assertContains($this->response1->first()->getCode(), ResponseCode::codes());
    }

    public function testGetFirstSmsStatusFromTwo()
    {
        $status = (new SmsStatus())
            ->of($this->response1->first()->getBatchNumber())
            ->of($this->response2->first()->getBatchNumber())
            ->first();

        $this->assertEquals($this->response1->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals($this->response1->first()->getMessage(), $status->text());
        $this->assertContains($this->response1->first()->getCode(), ResponseCode::codes());
    }

    public function testGetLastSmsStatusFromTwo()
    {
        $status = (new SmsStatus())
            ->of($this->response1->first()->getBatchNumber())
            ->of($this->response2->first()->getBatchNumber())
            ->last();

        $this->assertEquals($this->response2->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals($this->response2->first()->getMessage(), $status->text());
        $this->assertContains($this->response2->first()->getCode(), ResponseCode::codes());
    }

    public function testGetFirstSmsStatusFromTwoWithGet()
    {
        $status = (new SmsStatus())
            ->of($this->response1->first()->getBatchNumber())
            ->of($this->response2->first()->getBatchNumber())
            ->get($this->response1->first()->getBatchNumber());

        $this->assertEquals($this->response1->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals($this->response1->first()->getMessage(), $status->text());
        $this->assertContains($this->response1->first()->getCode(), ResponseCode::codes());
    }

    public function testGeLastSmsStatusFromTwoWithGet()
    {
        $status = (new SmsStatus())
            ->of($this->response1->first()->getBatchNumber())
            ->of($this->response2->first()->getBatchNumber())
            ->get($this->response2->first()->getBatchNumber());

        $this->assertEquals($this->response2->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals($this->response2->first()->getMessage(), $status->text());
        $this->assertContains($this->response2->first()->getCode(), ResponseCode::codes());
    }
}
