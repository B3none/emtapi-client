<?php

include('vendor/autoload.php');

$emtclient = \B3none\emtapi\EMTClient::create();
$stationConstantFactory = new \B3none\emtapi\Factories\StationConstantFactory();

if (file_exists('src/Station.php')) {
    unlink('src/Station.php');
}

$stationFile = fopen('src/Station.php', "w");

fwrite($stationFile, "<?php\n\n");
fwrite($stationFile, "namespace B3none\\emtapi;\n\n");
fwrite($stationFile, "abstract class Station\n");
fwrite($stationFile, "{\n");

$indentation = "    ";
fwrite($stationFile, $indentation . "// Created at: " . date('d/m/Y h:i A') . "\n");
try {
    $categories = $emtclient->getStations();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
foreach ($categories as $category) {
    foreach ($category as $station) {
        $constructedLine = "const " . $stationConstantFactory->create($station) . " = \"" . str_replace('"', '\\"', $station['label']) . "\";";

        fwrite($stationFile, $indentation . $constructedLine . "\n");
    }
}

fwrite($stationFile, "}");

fclose($stationFile);