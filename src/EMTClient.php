<?php

namespace B3none\emtapi;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class EMTClient
{
    const EMT_BASE_URL = "https://www.eastmidlandstrains.co.uk";
    const EMT_API_ENDPOINT = "/services/LiveTrainInfoService.svc/GetLiveBoardJson";

    /**
     * @var bool
     */
    protected $throwException;

    /**
     * @var Client
     */
    protected $client;

    public function __construct(bool $throwException = false)
    {
        $this->throwException = $throwException;
        $this->client = new Client(['cookies' => true]);
    }

    /**
     * Get the live details on a journey.
     *
     * Please note:
     * - You must make sure that the station names you input are correct.
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

        return $this->processResponse($requestResult);
    }

    /**
     * Process the response from the API call.
     *
     * @param string $result
     * @return array
     */
    protected function processResponse(string $result) : array
    {
        $result = json_decode($result, true);
        $decodedResponse = json_decode($result['d'], true);

        return $this->formatAndValidateResponse($decodedResponse);
    }

    /**
     * @param array $response
     * @return array
     * @throws \Exception
     */
    protected function formatAndValidateResponse(array $response) : array
    {
        if ($response['originnotfound']) {
            if ($this->throwException) {
                throw new \Exception('The origin station was not found.');
            } else {
                return ['errorMessage' => 'The origin station was not found.'];
            }
        } else if ($response['destnotfound']) {
            if ($this->throwException) {
                throw new \Exception('The destination station was not found.');
            } else {
                return ['errorMessage' => 'The destination station was not found.'];
            }
        } else if (!$response['buses'] && !$response['trains']) {
            if ($this->throwException) {
                throw new \Exception('There are no trains or buses.');
            } else {
                return ['errorMessage' => 'There are no trains or buses.'];
            }
        }

        foreach ($response as $paramKey => $responseParam) {
            if (!$responseParam) {
                unset($response[$paramKey]);
            }
        }

        return $response;
    }
}