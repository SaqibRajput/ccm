<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 09/02/2022
     * Time: 11:54
     */

    return [
        'skip_call' => env('BOOMI_SKIP_CALL', false),
        'time_out' => env('BOOMI_TIME_OUT', 10),
        'base_url' => env('BOOMI_URL'),
        'apis' => [
            'addressbook' => [
                'endpoint' => 'ws/rest/jde/addressBooks/',
                'method' => 'GET',
                'params' => [
                    'verify' => false, // need to verfiy and removed this key from all indexs
                    'auth'   => [
                        env('BOOMI_USERNAME'),
                        env('BOOMI_PASSWORD')
                    ]
                ]
            ],
            'create_address_book' => [
                'endpoint' => 'ws/rest/jde/addressBooks/',
                'method' => 'POST',
                'params' => [
                    'verify' => false, // need to verfiy and removed this key from all indexs
                    'auth'   => [
                        env('BOOMI_USERNAME'),
                        env('BOOMI_PASSWORD')
                    ]
                ]
            ],
            'zip_code' => [
                'endpoint' => 'ws/rest/jde/postalCode/',
                'method' => 'GET',
                'params' => [
                    'verify' => false, // need to verfiy and removed this key from all indexs
                    'auth'   => [
                        env('BOOMI_USERNAME'),
                        env('BOOMI_PASSWORD')
                    ]
                ]
            ],

            'price' => [
                'endpoint' => 'ws/rest/jde/orders/getpriceandavailability',
                'method' => 'POST',
                'params'   => [
                    'verify' => false,
                    'auth'   => [
                        env('BOOMI_USERNAME'),
                        env('BOOMI_PASSWORD')
                    ]
                ]
            ],

            'tax' => [
                'endpoint' => 'ws/rest/jde/orders/taxFreight',
                'method' => 'POST',
                'params'   => [
                    'verify' => false,
                    'auth'   => [
                        env('BOOMI_USERNAME'),
                        env('BOOMI_PASSWORD')
                    ]
                ]
            ],

            'create_provision' => [
                'endpoint' => 'ws/rest/ccm/provisioncreate',
                'method' => 'POST',
                'params'   => [
                    'verify' => false,
                    'auth'   => [
                        env('BOOMI_USERNAME'),
                        env('BOOMI_PASSWORD')
                    ]
                ]
            ],

            'boomi' => [
                'endpoint' => 'ws/rest/worldPay/registerToken',
                'method' => 'POST',
                'params'   => [
                    'verify' => false,
                    'auth'   => [
                        env('BOOMI_USERNAME'),
                        env('BOOMI_PASSWORD')
                    ]
                ]
            ],

            'suppliers_list' => [
                'endpoint' => 'ws/rest/ccm/supplierselection',
                'method' => 'POST',
                'params'   => [
                    'verify' => false,
                    'auth'   => [
                        env('BOOMI_USERNAME'),
                        env('BOOMI_PASSWORD')
                    ]
                ]
            ],

            'charity_academic_lookup' => [
                'endpoint' => 'ws/rest/ccm/academiccharity/',
                'method' => 'POST',
                'params'   => [
                    'verify' => false,
                    'auth'   => [
                        env('BOOMI_USERNAME'),
                        env('BOOMI_PASSWORD')
                    ]
                ]
            ],


        ]
    ];
