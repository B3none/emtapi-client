<?php

namespace B3none\emtapi\Tests;

use B3none\emtapi\EMTClient;
use B3none\emtapi\Station;
use PHPUnit\Framework\TestCase;

class EMTClientTest extends TestCase
{
    public function test()
    {
        $client = EMTClient::create();

        $client->createStationsFile();

        $testData = [];
//        $testData = $client->getJourneys('Derby', 'Burton-On-Trent');
        $this->assertTrue(is_array($testData));
    }
}