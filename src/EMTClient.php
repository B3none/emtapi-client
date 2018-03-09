<?php

namespace B3none\emtapi;

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

    public function __construct()
    {
        $this->client = new Client();
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

        return $this->processResponse($requestResult);
    }

    /**
     * Process the response from the API call.
     *
     * @param string $result
     * @return array
     * @throws \Exception
     */
    protected function processResponse(string $result) : array
    {
        $result = json_decode($result, true);

        if (!$result || empty($result)) {
            return ['errorMessages' => ['#1' => 'There was no result to process.']];
        }

        $decodedResponse = json_decode($result['d'], true);

        if (!$decodedResponse || empty($decodedResponse)) {
            return ['errorMessages' => ['#1' => 'There was no result to process.']];
        }

        return $this->checkResponse($decodedResponse);
    }

    /**
     * @param array $response
     * @return array
     * @throws \Exception
     */
    protected function checkResponse(array $response) : array
    {
        $errors = [];
        foreach ($response as $key => $value) {
            if ($key === 'originnotfound'&& !!$value) {
                $errors['#' . (count($errors) + 1)] = 'The origin station was not found.';
            }

            if ($key === 'equalsstations'&& !!$value) {
                $errors['#' . (count($errors) + 1)] = 'The stations were the same.';
            }

            if ($key === 'destnotfound' && !!$value) {
                $errors['#' . (count($errors) + 1)] = 'The destination station was not found.';
            }

            if ($key === 'buses' && !$value) {
                $errors['#' . (count($errors) + 1)] = 'There are no buses for this route.';
            }

            if ($key === 'trains' && !$value) {
                $errors['#' . (count($errors) + 1)] = 'There are no trains for this route.';
            }

            if (!$value) {
                unset($response[$key]);
            }
        }

        if (!empty($errors)) {
            $response['errorMessages'] = $errors;
        }

        return $response;
    }
}