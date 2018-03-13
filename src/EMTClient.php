<?php

namespace B3none\emtapi;

use B3none\emtapi\Factories\ParameterFactory;
use B3none\emtapi\Processors\ResponseProcessor;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class EMTClient
{
    const EMT_BASE_URL = "https://www.eastmidlandstrains.co.uk";
    const EMT_API_LIVE_TIMES = "/services/LiveTrainInfoService.svc/GetLiveBoardJson";

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
        $response = $this->client->request("POST", self::EMT_API_LIVE_TIMES, [
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
}