<?php

namespace Tests\Unit;

use Prinx\Txtconnect\Inbox;
use Tests\TestCase;

class InboxTest extends TestCase
{
    public function testGettingInbox()
    {
        $inbox = (new Inbox())->fetch();

        $this->assertIsInt($inbox->count());
        $this->assertIsArray($inbox->toArray());
        $this->assertIsArray($inbox->all());
    }
}
