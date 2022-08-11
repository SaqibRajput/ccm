<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 22/12/2021
     * Time: 18:26
     */

    return [
        'TYPE_CUSTOMER' => 'customer',
        'SIGNUP_STEPS' => [
            'STEP1' => 1,
            'VERIFY_EMAIL' => 2,
            'BUSINESS_INFORMATION' => 3,
            'USER_INFORMATION' => 4,
            //'ADDRESS_OF_USE' => 5,
            'SUBSCRIPTION_PLAN' => 5,
            'AGREEMENT' => 6,
            'LAST_STEP' => 7
        ],
        'ACCOUNT_LOGO_PATH' => 'logo',
        'tenant_status' => ['pending', 'active', 'terminate'],
        'tenant_state' => ['Waiting for Input', 'Ready'],
        'platform' => ['csv']
    ];
