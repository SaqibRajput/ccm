<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 22/12/2021
     * Time: 18:26
     */

    return [
        'skip_call' => env('ESG_SKIP_CALL', true),
        'time_out' => env('ESG_TIME_OUT', 30),
        'base_url' => env('ESG_URL'),
        'apis' => [
            'authentication' => [
                'endpoint' => 'rest/auth/oauth2',
                'method' => 'post',
                'params'   => [
                    'username' => env('ESG_USERNAME'),
                    'password' => env('ESG_PASSWORD'),
                    'grant_type' => 'password'
                ]
            ],
            'search' => [
                'endpoint' => 'rest/users/email/{email}',
                'method' => 'get'
            ],
            'account' => [
                'endpoint' => 'rest/account',
                'method' => 'get',
                'params'   => [
                    'accountCode' => ''
                ]
            ],
            'productId' => [
                'endpoint' => 'rest/products/supp_code/PB/supp_part/{suppPart}',
                'method' => 'get',
            ],
            'price' => [
                'endpoint' => 'rest/account/{accountCode}/products/supplier/part/{supplierPartId}/availabilityPricing',
                'method' => 'get',
            ],
            'product_sku' => [
                'endpoint' => 'rest/products/supp_code/PB/supp_part/{sku_id}',
                'method' => 'get'
            ],
            'user_ship_address' => [
                'endpoint' => 'rest/address/shipping?accountCode={account_code}',
                'method' => 'get'
            ],
            'add_to_cart' => [
                'endpoint' => 'rest/carts?accountId={account_id}',
                'method' => 'post'
            ],
            'clear_cart' => [
                'endpoint' => 'rest/carts?accountId={account_id}',
                'method' => 'delete'
            ],
            'user_cart' => [
                'endpoint' => 'rest/carts?accountId={account_id}',
                'method' => 'get'
            ],
            'create_order' => [
                'endpoint' => 'rest/orders?accountId={account_id}',
                'method' => 'post'
            ]
        ]

    ];
