<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Balance;
use Tests\TestCase;

class BalanceTest extends TestCase
{
    /**
     * Enable VCR on this test by removing the space between '@' and 'vcr' below.
     *
     * @ vcr get-balance.json.
     */
    public function testCanGetBalanceSuccessfully()
    {
        $balance = new Balance();
        $this->assertIsInt($balance->amount());
        $this->assertIsString($balance->user());
        $this->assertEquals('Ghana', $balance->country());
    }
}
