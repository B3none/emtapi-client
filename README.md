# emtapi-client
East Midlands Trains API Client written in PHP.

# Composer
```$xslt
composer require b3none/emtapi
```

# Example use
```php
$emtapi = new \B3none\emtapi\EMTClient();
$emtapi->getJourneys('Derby', 'Nottingham');
```

# Example response
```php
[
    'trains' => [
        0 => [
            'locationname' => 'Nottingham',
            'st' => '16:40',
            'et' => 'On time',
            'cssclass' => null,
            'operator' => 'CrossCountry',
            'trainid' => '+eRxw6w7A8UJsGRGIAT5Ag==',
            'platform' => '6A',
            'selecteddets_sta' => '16:34',
            'selecteddets_location' => 'Derby',
            'selecteddets_length' => '23 minutes',
            'selecteddets_prevlength' => '2 hours, 55 minutes',
            'callingpoints' => [
                '0' => [
                    'locationname' => 'Long Eaton',
                    'st' => '16:34',
                    'et' => 'On time',
                    'cssclass' => null
                ]
            ],
            'previouscallingpoints' => [
                0 => [
                    'locationname' => 'Cardiff Central',
                    'st' => '13:45',
                    'et' => null,
                    'cssclass' => 'circle-full',
                    'at' => 'On time'
                ]
            ]
        ]
    ]
]

```

# Credits
* Alex Blackham (ablackham2000@gmail.com)
