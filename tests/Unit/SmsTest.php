<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Lib\ResponseCode;
use Prinx\Txtconnect\Lib\SmsResponse;
use Prinx\Txtconnect\Lib\SmsResponseBag;
use Prinx\Txtconnect\Sms;
use Prinx\Txtconnect\SmsStatus;
use Tests\TestCase;
use function Prinx\Dotenv\env;

class SmsTest extends TestCase
{
    protected static $message;
    protected static $originalNumber;
    protected static $originalNumber2;
    protected static $parsedNumber;
    protected static $parsedNumber2;

    /**
     * @var SmsResponse
     */
    protected static $response1;

    /**
     * @var SmsResponse
     */
    protected static $response2;

    /**
     * @var SmsResponseBag
     */
    protected static $response3;

    /**
     * @var SmsResponseBag
     */
    protected static $response4;

    /**
     * @vcr send-successful-sms.json
     */
    public static function setUpBeforeClass(): void
    {
        self::$message = 'Hi';
        
        self::$originalNumber = env('TEST_PHONE1');
        self::$parsedNumber = env('TEST_PHONE1_PARSED');

        self::$originalNumber2 = env('TEST_PHONE2');
        self::$parsedNumber2 = env('TEST_PHONE2_PARSED');

        self::$response1 = (new Sms())->send(self::$message, self::$originalNumber);

        self::$response2 = (new Sms())->to(self::$originalNumber)->send(self::$message);
        
        self::$response3 = (new Sms())->country('GH')
        ->keepDuplicate()
        ->send(self::$message, [self::$originalNumber, self::$originalNumber2]);

        var_dump(self::$response3);
        
        self::$response4 = (new Sms())->asBag()->send(self::$message, self::$originalNumber);
    }

    public function testReturnProperResponse()
    {
        $this->assertInstanceOf(SmsResponse::class, self::$response1, 'Response 1 must be an instance of SmsResponse');
        $this->assertInstanceOf(SmsResponse::class, self::$response2, 'Response 2 must be an instance of SmsResponse');
        $this->assertInstanceOf(SmsResponseBag::class, self::$response3, 'Response 3 must be an instance of SmsResponseBag');
        $this->assertInstanceOf(SmsResponseBag::class, self::$response4, 'Response 4 must be an instance of SmsResponseBag even though the sms was sent to only number, because we called asBag method on the Sms instance before sending the sms.');
    }

    public function testCanSendSuccessfullySms()
    {
        $this->assertTrue(self::$response1->isBeingProcessed());
        $this->assertTrue(self::$response2->isBeingProcessed());
        $this->assertTrue(self::$response3->isBeingProcessed());
        $this->assertTrue(self::$response4->isBeingProcessed());
    }

    public function testIsBeingProcessed()
    {
        $this->assertTrue(self::$response3->first()->isBeingProcessed());
        $this->assertTrue(self::$response3->last()->isBeingProcessed());

        $this->assertTrue(self::$response3->get(self::$originalNumber)->isBeingProcessed());
        $this->assertTrue(self::$response3->get(self::$originalNumber2)->isBeingProcessed());
    }

    public function testGettingProperFirst()
    {
        $this->assertSame(self::$response3->first(), self::$response3->get(self::$originalNumber));
    }

    public function testGettingProperLast()
    {
        $this->assertSame(self::$response3->last(), self::$response3->get(self::$originalNumber2));
    }

    public function testResolvingNumbersWell()
    {
        $this->assertEquals([
            self::$originalNumber => self::$parsedNumber,
            self::$originalNumber2 => self::$parsedNumber2,
        ], self::$response3->numbers());
        $this->assertEquals(
            [self::$originalNumber, self::$originalNumber2],
            self::$response3->originalNumbers()
        );

        $this->assertEquals(self::$parsedNumber, self::$response3->first()->getParsedNumber());
        $this->assertEquals(self::$parsedNumber2, self::$response3->last()->getParsedNumber());

        $this->assertEquals(self::$originalNumber, self::$response3->first()->getOriginalNumber());
        $this->assertEquals(self::$originalNumber2, self::$response3->last()->getOriginalNumber());

        $this->assertEquals(self::$parsedNumber, self::$response3->get(self::$originalNumber)->getParsedNumber());
        $this->assertEquals(self::$parsedNumber, self::$response3->get(self::$originalNumber)->getParsedNumber());

        $this->assertEquals(self::$originalNumber, self::$response3->get(self::$originalNumber)->getOriginalNumber());
        $this->assertEquals(self::$originalNumber, self::$response3->get(self::$originalNumber)->getOriginalNumber());
    }

    public function testGettingRightError()
    {
        $this->assertNull(self::$response3->getError());
        $this->assertNull(self::$response3->first()->getError());
    }

    public function testGettingRightCode()
    {
        $this->assertEquals(self::$response3->first()->getCode(), ResponseCode::OK);
    }

    public function testUsernameIsString()
    {
        $this->assertIsString(self::$response3->first()->getUserName());
    }

    public function testStatusCheckUrlIsString()
    {
        $this->assertIsString(self::$response3->first()->getStatusCheckUrl());
    }

    public function testRawResponseIsString()
    {
        $this->assertIsString(self::$response3->first()->getRawResponse());
    }

    public function testBatchNumberIsString()
    {
        $this->assertIsString(self::$response3->first()->getBatchNumber());
    }

    /**
     * @vcr testGetOneSmsStatusWithGet.json
     */
    public function testGetOneSmsStatusWithGet()
    {
        $status = (new SmsStatus())->of(self::$response3->first()->getBatchNumber())->get();

        var_dump($status->content());
        $this->assertEquals(self::$response3->first()->getParsedNumber(), $status->recipient());
        var_dump(self::$response3->first()->getMessage(), $status->text());
        $this->assertEquals(self::$response3->first()->getSms(), $status->text());
        $this->assertContains(self::$response3->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGetOneSmsStatusWithFirst.json
     */
    public function testGetOneSmsStatusWithFirst()
    {
        $status = (new SmsStatus())->of(self::$response3->first()->getBatchNumber())->first();
        $this->assertEquals(self::$response3->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response3->first()->getSms(), $status->text());
        $this->assertContains(self::$response3->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGetOneSmsStatusWithLast.json
     */
    public function testGetOneSmsStatusWithLast()
    {
        $status = (new SmsStatus())->of(self::$response3->first()->getBatchNumber())->last();
        $this->assertEquals(self::$response3->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response3->first()->getSms(), $status->text());
        $this->assertContains(self::$response3->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGetFirstSmsStatusFromTwo.json
     */
    public function testGetFirstSmsStatusFromTwo()
    {
        $status = (new SmsStatus())
            ->of(self::$response3->first()->getBatchNumber())
            ->of(self::$response2->getBatchNumber())
            ->first();

        $this->assertEquals(self::$response3->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response3->first()->getSms(), $status->text());
        $this->assertContains(self::$response3->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGetLastSmsStatusFromTwo.json
     */
    public function testGetLastSmsStatusFromTwo()
    {
        $status = (new SmsStatus())
            ->of(self::$response3->first()->getBatchNumber())
            ->of(self::$response2->getBatchNumber())
            ->last();

        $this->assertEquals(self::$response2->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response2->getSms(), $status->text());
        $this->assertContains(self::$response2->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGetFirstSmsStatusFromTwoWithGet.json
     */
    public function testGetFirstSmsStatusFromTwoWithGet()
    {
        $status = (new SmsStatus())
            ->of(self::$response3->first()->getBatchNumber())
            ->of(self::$response2->getBatchNumber())
            ->get(self::$response3->first()->getBatchNumber());

        $this->assertEquals(self::$response3->first()->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response3->first()->getSms(), $status->text());
        $this->assertContains(self::$response3->first()->getCode(), ResponseCode::codes());
    }

    /**
     * @vcr testGeLastSmsStatusFromTwoWithGet.json
     */
    public function testGetLastSmsStatusFromTwoWithGet()
    {
        $status = (new SmsStatus())
            ->of(self::$response3->first()->getBatchNumber())
            ->of(self::$response2->getBatchNumber())
            ->get(self::$response2->getBatchNumber());

        $this->assertEquals(self::$response2->getParsedNumber(), $status->recipient());
        $this->assertEquals(self::$response2->getSms(), $status->text());
        $this->assertContains(self::$response2->getCode(), ResponseCode::codes());
    }
}
