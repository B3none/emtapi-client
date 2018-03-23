<?php

namespace B3none\emtapi;

use B3none\emtapi\Factories\ParameterFactory;
use B3none\emtapi\Factories\StationConstantFactory;
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

    /**
     * EMTClient constructor.
     *
     * @param Client $client
     * @param ResponseProcessor $responseProcessor
     * @param ParameterFactory $parameterFactory
     */
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
     *   you input are correct or the API will
     *   error.
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
           RequestOptions::BODY => $this->parameterFactory->create($startLocation, $endLocation, $departure)
        ]);

        $requestResult = $response->getBody()->getContents();

        return $this->responseProcessor->processResponse($requestResult);
    }

    /**
     * This function grabs a list of all train stations in their API.
     *
     * @return array
     * @throws \Exception
     */
    public function getStations() : array
    {
        $request = $this->client->request("GET", self::EMT_STATIONS);

        $body = $request->getBody()->getContents();

        if (!$body) {
            throw new \Exception('There was no body');
        }

        $startPos = mb_strpos($body, "{");
        $endPos = mb_strrpos($body, "}");

        if ($startPos === false || $endPos === false) {
            throw new \Exception("Could not detect the ". ($startPos === false ? "start":"end") ." of the JSON.");
        }

        $newBody = mb_substr($body, $startPos, ($endPos - $startPos) + 1);

        $contents = json_decode($newBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Could not parse JSON: ".json_last_error_msg());
        }

        return $contents;
    }
}