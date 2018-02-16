<?php

namespace B3none\emtapi\Tests;

use B3none\emtapi\EMTClient;
use PHPUnit\Framework\TestCase;

class EMTClientTest extends TestCase
{
    public function test()
    {
        $client = new EMTClient();
        $testData = $client->getJourneys('Derby', 'Burton-Upon-Trent');
        $this->assertTrue(is_array($testData));
    }
}