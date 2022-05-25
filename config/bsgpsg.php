<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 22/12/2021
     * Time: 18:26
     */

    return [
        'skip_call' => env('UPS_SKIP_CALL', false),
        'time_out' => env('UPS_TIME_OUT', 10),
        'base_url' => env('UPS_URL'),
        'auth_url' => env('UPS_AUTH_URL'),
        'apis' => [
            'authentication' => [
                'endpoint' => env('UPS_AUTH_ENDPOINT','/api/v1/Authenticate'),
                'method' => 'post',
                'params' => [
                    'verify' => false,
                    'auth'   => [
                        env('UPS_USERNAME'),
                        env('UPS_PASSWORD')
                    ]
                ],
                'json' => [
                    "authenticatedUserEmailAddress" => env('UPS_JSON_EMAIL'),
                    "impersonatingUserEmailAddress" => env('UPS_AUTH_PARAM_IMPERSONATING_USER_EMAIL_ADDRESS',null),
                    "isAdUser" => env('UPS_AUTH_PARAM_IS_AD_USER',false),
                    "domain" => env('UPS_AUTH_PARAM_DOMAIN',null),
                    "delegateAs" => env('UPS_AUTH_PARAM_DELEGATE_AS',"00000000-0000-0000-0000-000000000000"),
                    "webSiteSource" => env('UPS_AUTH_PARAM_WEBSITE_SOURCE',null)
                ]
            ],
            'search' => [
                'endpoint' => env('UPS_SEARCH_ENDPOINT','/api/v1/Users/Search'),
                'method' => 'get',
                'params'   => [
                    'verify' => env('UPS_SEARCH_PARAM_VERIFY',false)
                ]
            ],
            'groups' => [
                'endpoint' => env('UPS_GROUPS_ENDPOINT','/api/v1/Users/{user_id}/Groups'),
                'method' => 'get',
                'params'   => [
                    'verify' => env('UPS_GROUPS_PARAM_VERIFY',false)
                ]
            ],
            'groupDetail' => [
                'endpoint' => env('UPS_GROUP_DETAIL_ENDPOINT','/api/v1/Groups/'),
                'method' => 'get',
            ]
        ]
    ];
