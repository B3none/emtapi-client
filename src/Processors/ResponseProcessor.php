<?php

namespace B3none\emtapi\Processors;

class ResponseProcessor
{
    /**
     * Process the response from the API call.
     *
     * @param string $result
     * @return array
     * @throws \Exception
     */
    public function processResponse(string $result) : array
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