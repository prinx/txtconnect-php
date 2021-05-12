<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Contracts\SmsResponseBagInterface;
use Prinx\Txtconnect\ResponseCode;
use Prinx\Txtconnect\Sms;
use Tests\TestCase;

class SmsTest extends TestCase
{
    public function setUp(): void
    {
        $this->message = 'Hi';
        $this->originalNumber = '233(0) 54 54-66-796';
        $this->parsedNumber = '233545466796';
    }

    /**
     * @vcr send-successful-sms.json
     */
    public function testCanSendSuccessfullySms()
    {
        $sms = new Sms();

        $response = $sms->country('GH')->send($this->message, $this->originalNumber);

        $this->assertTrue($response->isBeingProcessed());

        return $response;
    }

    /**
     * @depends testCanSendSuccessfullySms
     *
     * @param SmsResponseBagInterface $response
     */
    public function testIsOk($response)
    {
        $this->assertTrue($response->first()->isOk());
        $this->assertTrue($response->last()->isOk());

        $this->assertTrue($response->get($this->originalNumber)->isOk());
        $this->assertTrue($response->get($this->originalNumber)->isOk());

        $this->assertTrue($response->get($this->parsedNumber)->isOk());
        $this->assertTrue($response->get($this->parsedNumber)->isOk());
    }

    /**
     * @depends testCanSendSuccessfullySms
     *
     * @param SmsResponseBagInterface $response
     */
    public function testGettingProperFirst($response)
    {
        $this->assertSame($response->first(), $response->get($this->originalNumber));
        $this->assertSame($response->first(), $response->get($this->parsedNumber));
    }

    /**
     * @depends testCanSendSuccessfullySms
     *
     * @param SmsResponseBagInterface $response
     */
    public function testGettingProperLast($response)
    {
        $this->assertSame($response->last(), $response->get($this->originalNumber));
        $this->assertSame($response->last(), $response->get($this->parsedNumber));
    }

    /**
     * @depends testCanSendSuccessfullySms
     *
     * @param SmsResponseBagInterface $response
     */
    public function testResolvingNumbersWell($response)
    {
        $this->assertEquals([$this->originalNumber => $this->parsedNumber], $response->numbers());
        $this->assertEquals([$this->originalNumber], $response->originalNumbers());

        $this->assertEquals($this->parsedNumber, $response->first()->getParsedNumber());
        $this->assertEquals($this->parsedNumber, $response->last()->getParsedNumber());

        $this->assertEquals($this->originalNumber, $response->first()->getOriginalNumber());
        $this->assertEquals($this->originalNumber, $response->last()->getOriginalNumber());

        $this->assertEquals($this->parsedNumber, $response->get($this->originalNumber)->getParsedNumber());
        $this->assertEquals($this->parsedNumber, $response->get($this->originalNumber)->getParsedNumber());

        $this->assertEquals($this->parsedNumber, $response->get($this->parsedNumber)->getParsedNumber());
        $this->assertEquals($this->parsedNumber, $response->get($this->parsedNumber)->getParsedNumber());

        $this->assertEquals($this->originalNumber, $response->get($this->originalNumber)->getOriginalNumber());
        $this->assertEquals($this->originalNumber, $response->get($this->originalNumber)->getOriginalNumber());

        $this->assertEquals($this->originalNumber, $response->get($this->parsedNumber)->getOriginalNumber());
        $this->assertEquals($this->originalNumber, $response->get($this->parsedNumber)->getOriginalNumber());
    }

    /**
     * @depends testCanSendSuccessfullySms
     *
     * @param SmsResponseBagInterface $response
     */
    public function testGettingRightError($response)
    {
        $this->assertNull($response->getError());
        $this->assertNull($response->first()->getError());
    }

    /**
     * @depends testCanSendSuccessfullySms
     *
     * @param SmsResponseBagInterface $response
     */
    public function testGettingRightCode($response)
    {
        $this->assertEquals($response->first()->getCode(), ResponseCode::OK);
    }

    /**
     * @depends testCanSendSuccessfullySms
     *
     * @param SmsResponseBagInterface $response
     */
    public function testBatchNumberIsString($response)
    {
        $this->assertIsString($response->first()->getBatchNumber());
    }

    /**
     * @depends testCanSendSuccessfullySms
     *
     * @param SmsResponseBagInterface $response
     */
    public function testUsernameIsString($response)
    {
        $this->assertIsString($response->first()->getUserName());
    }

    /**
     * @depends testCanSendSuccessfullySms
     *
     * @param SmsResponseBagInterface $response
     */
    public function testStatusCheckUrlIsString($response)
    {
        $checkUrl = $response->first()->getStatusCheckUrl();
        $this->assertIsString($checkUrl);
    }

    /**
     * @depends testCanSendSuccessfullySms
     *
     * @param SmsResponseBagInterface $response
     */
    public function testRawResponseIsString($response)
    {
        $this->assertIsString($response->first()->getRawResponse());
    }
}
