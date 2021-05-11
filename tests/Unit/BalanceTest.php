<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Balance;
use Tests\TestCase;
use VCR\VCR;

class BalanceTest extends TestCase
{
    /**
     * @vcr get-balance.json.
     */
    public function testCanGetBalanceSuccessfully()
    {
        $balance = new Balance();
        $this->assertIsInt($balance->value());
    }
}
