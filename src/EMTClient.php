<?php

namespace B3none\emtapi;

use B3none\emtapi\Factories\ParameterFactory;
use B3none\emtapi\Processors\ResponseProcessor;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class EMTClient
{
    const EMT_BASE_URL = "https://www.eastmidlandstrains.co.uk";
    const EMT_TIMES = "/services/LiveTrainInfoService.svc/GetLiveBoardJson";
    const EMT_STATIONS = "/emt/handlers/NRESStationList.ashx?v=1.2&titlecase=True";

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ResponseProcessor
     */
    protected $responseProcessor;

    /**
     * @var ParameterFactory
     */
    protected $parameterFactory;

    public static function create()
    {
        return new self(new Client(["base_uri" => self::EMT_BASE_URL]), new ResponseProcessor(), new ParameterFactory());
    }

    public function __construct(Client $client, ResponseProcessor $responseProcessor, ParameterFactory $parameterFactory)
    {
        $this->client = $client;
        $this->responseProcessor = $responseProcessor;
        $this->parameterFactory = $parameterFactory;
    }

    /**
     * Get the live details on a journey.
     *
     * Please note:
     * - You must make sure that the station names
     *   you input are correct or the API will error.
     *
     * @param string $startLocation
     * @param string $endLocation
     * @param bool $departure
     * @return array
     * @throws \Exception
     */
    public function getJourneys(string $startLocation, string $endLocation, bool $departure = true) : array
    {
        $response = $this->client->request("POST", self::EMT_TIMES, [
           RequestOptions::HEADERS => [
               "Content-Type" => "application/json"
           ],
           RequestOptions::FORM_PARAMS => $this->parameterFactory->create($startLocation, $endLocation, $departure)
        ]);

//        $request = new Request('POST', self::EMT_API_ENDPOINT, [
//            'Content-Type' => 'application/json'
//        ], $jsonData);
//
//        $response = $this->client->send($request);
        $requestResult = $response->getBody()->getContents();

        return $this->responseProcessor->processResponse($requestResult);
    }

    protected function getStations()
    {
        $request = $this->client->request("GET", self::EMT_STATIONS);

        $body = trim($request->getBody()->getContents());
        if (!$body) {
            throw new \Exception('There was no body');
        }

        $contents = json_decode($body, true);

        if (!$contents) {
            $contents = json_decode(substr($body, 3), true);
            if (!$contents) {
                throw new \Exception('Could not parse JSON: ' . $body);
            }
        }

        return $contents;
    }

    protected function getConstantName(array $station)
    {
        $label = $station['label'];

        $label = strtoupper($label);
        $label = str_replace(" ", "_", $label);
        $label = str_replace(",", "_", $label);
        $label = str_replace("-", "_", $label);
        $label = str_replace("&", "AND", $label);
        $label = str_replace("(", "", $label);
        $label = str_replace(")", "", $label);
        $label = str_replace("'", "", $label);

        return $label;
    }

    public function createStationsFile()
    {
        if (file_exists('src/Station.php')) {
            unlink('src/Station.php');
        }

        $stationFile = fopen('src/Station.php', "w");

        fwrite($stationFile, "<?php\n\n");
        fwrite($stationFile, "namespace B3none\\emtapi;\n\n");
        fwrite($stationFile, "abstract class Station\n");
        fwrite($stationFile, "{\n");

        $indentation = "    ";
        $stations = $this->getStations();
        foreach ($stations as $categories) {
            foreach ($categories as $station) {
                $constructedLine = "const " . $this->getConstantName($station) . " = \"" . str_replace('"', '\\"', $station['label']) . "\";";

                fwrite($stationFile, $indentation . $constructedLine . "\n");
            }
        }

        fwrite($stationFile, "}\n");

        return fclose($stationFile);
    }
}