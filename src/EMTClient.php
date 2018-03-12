<?php

namespace B3none\emtapi;

use B3none\emtapi\Processors\ResponseProcessor;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class EMTClient
{
    const EMT_BASE_URL = "https://www.eastmidlandstrains.co.uk";
    const EMT_API_ENDPOINT = "/services/LiveTrainInfoService.svc/GetLiveBoardJson";

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ResponseProcessor
     */
    protected $responseProcessor;

    public function __construct(Client $client = null, ResponseProcessor $responseProcessor = null)
    {
        $this->client = $client || new Client();
        $this->responseProcessor = $responseProcessor || new ResponseProcessor();
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
     * @return array
     * @throws \Exception
     */
    public function getJourneys(string $startLocation, string $endLocation) : array
    {
        if ($startLocation == $endLocation) {
            throw new \Exception("The start and end station must not be the same.");
        }

        $jsonData = json_encode([
            'request' => [
                'OriginText' => $startLocation,
                'DestText' => $endLocation,
                'Departures' => true
            ],
        ]);

        $request = new Request('POST', self::EMT_BASE_URL . self::EMT_API_ENDPOINT, [
            'Content-Type' => 'application/json'
        ], $jsonData);

        $response = $this->client->send($request);
        $requestResult = $response->getBody()->getContents();

        return $this->responseProcessor->processResponse($requestResult);
    }
}