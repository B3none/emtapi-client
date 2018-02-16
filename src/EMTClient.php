<?php

namespace B3none\emtapi;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class EMTClient
{
    const EMT_BASE_URL = "https://www.eastmidlandstrains.co.uk";
    const EMT_API_ENDPOINT = "/services/LiveTrainInfoService.svc/GetLiveBoardJson";

    const DEFAULT_SESSION_ID = "";

    /**
     * @param string $startLocation
     * @param string $endLocation
     * @return mixed
     */
    public function getJourneys(string $startLocation, string $endLocation)
    {
        $guzzleClient = new Client();

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

        $promise = $guzzleClient->sendAsync($request)->then(function ($response) {
            return $response->getBody()->getContents();
        });
        $promise->wait();

        var_dump($this->processResponse($promise));
        return $this->processResponse($promise);
    }

    /**
     * Grab a new session ID via GET request.
     *
     * @return string
     */
    protected function fetchNewSessionId()
    {
        $client = new Client(['cookies' => true]);
        $client->get(self::EMT_BASE_URL);

        $cookies = $client->getConfig('cookies')->toArray();

        foreach ($cookies as $cookie) {
            if ($cookie['Name'] === "ASP.NET_SessionId") {
                return $cookie['Value'];
            }
        }

        return self::DEFAULT_SESSION_ID;
    }

    protected function processResponse($response)
    {
        $response = json_decode($response, true);
        $encodedResponseData = json_encode($response['d']);

        return json_decode($encodedResponseData, true);
    }
}