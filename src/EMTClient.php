<?php

namespace B3none\emtapi;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class EMTClient
{
    const EMT_BASE_URL = "https://www.eastmidlandstrains.co.uk";
    const EMT_API_ENDPOINT = "/services/LiveTrainInfoService.svc/GetLiveBoardJson";

    const DEFAULT_SESSION_ID = "hoetyz55hfy5t1554cepm32i";

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
            'Host' => 'www.eastmidlandstrains.co.uk',
            'Connection' => 'keep-alive',
            'Content-Length' => strlen($jsonData),
            'Pragma' => 'no-cache',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Origin' => 'https://www.eastmidlandstrains.co.uk',
            'X-Requested-With' => 'XMLHttpRequest',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36',
            'Content-Type' => 'application/json',
            'Referer' => 'https://www.eastmidlandstrains.co.uk/train-times/live-train-updates',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'en-GB,en-US;q=0.8,en;q=0.6',
            'Cookie' => 'EMT_cookies=True; ASP.NET_SessionId=' . $this->fetchNewSessionId() . '; _gat=1; _gat_UA-32673593-2=1; _ga=GA1.3.1718692550.1518710881; _gid=GA1.3.1779475860.1518710881'
        ], $jsonData);

        $response = $this->client->send($request);
        $requestResult = $response->getBody()->getContents();

        return $this->processResponse($requestResult);
    }

    /**
     * Grab a new session ID via GET request.
     *
     * @return string
     */
    protected function fetchNewSessionId() : string
    {
        $this->client->get(self::EMT_BASE_URL);
        $cookies = $this->client->getConfig('cookies')->toArray();

        foreach ($cookies as $cookie) {
            if ($cookie['Name'] === "ASP.NET_SessionId") {
                return $cookie['Value'];
            }
        }

        return self::DEFAULT_SESSION_ID;
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