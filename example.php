<?php

include('vendor/autoload.php');

try{
    $emtapi = \B3none\emtapi\EMTClient::create();
    $emtapi->createStationsFile();
    print_r($emtapi->getJourneys(\B3none\emtapi\Station::DERBY, \B3none\emtapi\Station::NOTTINGHAM));
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage()."\n";
}