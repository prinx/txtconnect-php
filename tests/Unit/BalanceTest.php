<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Balance;
use Prinx\Txtconnect\Sms;
use Tests\TestCase;

class BalanceTest extends TestCase
{
    public function testCanGetBalanceSuccessfully()
    {
        $balance = new Balance();
        $this->assertIsInt($balance->value());
    }
}
