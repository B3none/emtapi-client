<?php

namespace B3none\emtapi\Factories;

class ParameterFactory
{
    public function create($startLocation, $endLocation, $departure)
    {
        return json_encode([
            'request' => [
                'OriginText' => $startLocation,
                'DestText' => $endLocation,
                'Departures' => $departure
            ],
        ]);
    }
}