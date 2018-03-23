<?php

include('vendor/autoload.php');

try{
    $emtapi = \B3none\emtapi\EMTClient::create();
    print_r($emtapi->getJourneys(\B3none\emtapi\Station::DERBY, \B3none\emtapi\Station::AMBERGATE));
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage()."\n";
}