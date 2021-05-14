<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Lib\ResponseCode;
use Prinx\Txtconnect\Sms;
use Prinx\Txtconnect\SmsStatus;
use Tests\TestCase;

class SmsTest extends TestCase
{
    protected static $message;
    protected static $originalNumber;
    protected static $parsedNumber;
    protected static $response1;
    protected static $response2;

    /**
     * @vcr send-successful-sms.json
     */
    public static function setUpBeforeClass(): void
    {
        self::$message = 'Hi';
        self::$originalNumber = '233(0) 54 54-66-796';
        self::$parsedNumber = '233545466796';
        
        self::$response1 = (new Sms())->country('GH')->send(self::$message, self::$originalNumber);
        self::$response2 = (new Sms())->country('GH')->send(self::$message, self::$originalNumber);
    }

    public function testCanSendSuccessfullySms()
    {
        $this->assertTrue(self::$response1->isBeingProcessed());
        $this->assertTrue(self::$response2->isBeingProcessed());
    }

    public function testIsOk()
    {
        $this->assertTrue(self::$response1->first()->isOk());
        $this->assertTrue(self::$response1->last()->isOk());

        $this->assertTrue(self::$response1->get(self::$originalNumber)->isOk());
        $this->assertTrue(self::$response1->get(self::$originalNumber)->isOk());

        $this->assertTrue(self::$response1->get(self::$parsedNumber)->isOk());
        $this->assertTrue(self::$response1->get(self::$parsedNumber)->isOk());
    }

    public function testGettingProperFirst()
    {
        $this->assertSame(self::$response1->first(), self::$response1->get(self::$originalNumber));
        $this->assertSame(self::$response1->first(), self::$response1->get(self::$parsedNumber));
    }

    public function testGettingProperLast()
    {
        $this->assertSame(self::$response1->last(), self::$response1->get(self::$originalNumber));
        $this->assertSame(self::$response1->last(), self::$response1->get(self::$parsedNumber));
    }

    public function testResolvingNumbersWell()
    {
        $this->assertEquals([self::$originalNumber => self::$parsedNumber], self::$response1->numbers());
        $this->assertEquals([self::$originalNumber], self::$response1->originalNumbers());

        $this->assertEquals(self::$parsedNumber, self::$response1->first()->getParsedNumber());
        $this->assertEquals(self::$parsedNumber, self::$response1->last()->getParsedNumber());

        $this->assertEquals(self::$originalNumber, self::$response1->first()->getOriginalNumber());
        $this->assertEquals(self::$originalNumber, self::$response1->last()->getOriginalNumber());

        $this->assertEquals(self::$parsedNumber, self::$response1->get(self::$originalNumber)->getParsedNumber());
        $this->assertEquals(self::$parsedNumber, self::$response1->get(self::$originalNumber)->getParsedNumber());

        $this->assertEquals(self::$parsedNumber, self::$response1->get(self::$parsedNumber)->getParsedNumber());
        $this->assertEquals(self::$parsedNumber, self::$response1->get(self::$parsedNumber)->getParsedNumber());

        $this->assertEquals(self::$originalNumber, self::$response1->get(self::$originalNumber)->getOriginalNumber());
        $this->assertEquals(self::$originalNumber, self::$response1->get(self::$originalNumber)->getOriginalNumber());

        $this->assertEquals(self::$originalNumber, self::$response1->get(self::$parsedNumber)->getOriginalNumber());
        $this->assertEquals(self::$originalNumber, self::$response1->get(self::$parsedNumber)->getOriginalNumber());
    }

    public function testGettingRightError()
    {
        $this->assertNull(self::$response1->getError());
        $this->assertNull(self::$response1->first()->getError());
    }

    public function testGettingRightCode()
    {
        $this->assertEquals(self::$response1->first()->getCode(), ResponseCode::OK);
    }

    public function testUsernameIsString()
    {
        $this->assertIsString(self::$response1->first()->getUserName());
    }

    public function testStatusCheckUrlIsString()
    {
        $this->assertIsString(self::$response1->first()->getStatusCheckUrl());
    }

    public function testRawResponseIsString()
    {
        $this->assertIsString(self::$response1->first()->getRawResponse());
    }

    public function testBatchNumberIsString()
    {
        $this->assertIsString(self::$response1->first()->getBatchNumber());
    }

    /**
     * @vcr testGetOneSmsStatusWithGet.json
     */
    public function testGetOneSmsStatusWithGet()
    {
        $status = (new SmsStatus())->of(self::$response1->first()->getBatchNumber())->get();

        var_dump($status->content());
        $this->assertEquals(self::$response1->first()->getParsedNumber(), $status->recipient());
        var_dump(self::$response1->first()->getMessage(), $status->text());
        $this->assertEquals(self::$response1->first()->getSms(), $status->text());
        $this->assertContains(self::$response1->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGetOneSmsStatusWithFirst.json
     */
    public function testGetOneSmsStatusWithFirst()
    {
        $status = (new SmsStatus())->of(self::$response1->first()->getBatchNumber())->first();
        $this->assertEquals(self::$response1->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response1->first()->getSms(), $status->text());
        $this->assertContains(self::$response1->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGetOneSmsStatusWithLast.json
     */
    public function testGetOneSmsStatusWithLast()
    {
        $status = (new SmsStatus())->of(self::$response1->first()->getBatchNumber())->last();
        $this->assertEquals(self::$response1->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response1->first()->getSms(), $status->text());
        $this->assertContains(self::$response1->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGetFirstSmsStatusFromTwo.json
     */
    public function testGetFirstSmsStatusFromTwo()
    {
        $status = (new SmsStatus())
            ->of(self::$response1->first()->getBatchNumber())
            ->of(self::$response2->first()->getBatchNumber())
            ->first();

        $this->assertEquals(self::$response1->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response1->first()->getSms(), $status->text());
        $this->assertContains(self::$response1->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGetLastSmsStatusFromTwo.json
     */
    public function testGetLastSmsStatusFromTwo()
    {
        $status = (new SmsStatus())
            ->of(self::$response1->first()->getBatchNumber())
            ->of(self::$response2->first()->getBatchNumber())
            ->last();

        $this->assertEquals(self::$response2->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response2->first()->getSms(), $status->text());
        $this->assertContains(self::$response2->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGetFirstSmsStatusFromTwoWithGet.json
     */
    public function testGetFirstSmsStatusFromTwoWithGet()
    {
        $status = (new SmsStatus())
            ->of(self::$response1->first()->getBatchNumber())
            ->of(self::$response2->first()->getBatchNumber())
            ->get(self::$response1->first()->getBatchNumber());

        $this->assertEquals(self::$response1->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response1->first()->getSms(), $status->text());
        $this->assertContains(self::$response1->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGeLastSmsStatusFromTwoWithGet.json
     */
    public function testGeLastSmsStatusFromTwoWithGet()
    {
        $status = (new SmsStatus())
            ->of(self::$response1->first()->getBatchNumber())
            ->of(self::$response2->first()->getBatchNumber())
            ->get(self::$response2->first()->getBatchNumber());

        $this->assertEquals(self::$response2->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response2->first()->getSms(), $status->text());
        $this->assertContains(self::$response2->first()->getCode(), ResponseCode::codes());
    }
}
