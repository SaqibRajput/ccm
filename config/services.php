<?php

    return [
        'authentication'   =>  [
            'base_uri'  =>  env('AUTHENTICATION_SERVICE_BASE_URL', 'http://ccm-authentication.local/'),
            'secret'  =>  env('AUTHENTICATION_SERVICE_SECRET', 'qB20xFEIaSEW8ZAsEimIIn7mBA0x68LB0TcER1FE'),
        ],
        'companies'   =>  [
            'base_uri'  =>  env('COMPANIES_SERVICE_BASE_URL', 'http://ccm-companies.local/'),
            'secret'  =>  env('COMPANIES_SERVICE_SECRET', 'qB20xFEIaSEW8ZAsEimIIn7mBA0x68LB0TcER1FE'),
        ],
        'catalogs'   =>  [
            'base_uri'  =>  env('CATALOGS_SERVICE_BASE_URL', 'http://ccm-catalogs.local/'),
            'secret'  =>  env('CATALOGS_SERVICE_SECRET', 'qB20xFEIaSEW8ZAsEimIIn7mBA0x68LB0TcER1FE'),
        ],
        'broadcast'   =>  [
            'base_uri'  =>  env('BROADCAST_SERVICE_BASE_URL', 'http://ccm-broadcast.local/'),
            'secret'  =>  env('BROADCAST_SERVICE_SECRET', 'qB20xFEIaSEW8ZAsEimIIn7mBA0x68LB0TcER1FE'),
        ],
        'logs'   =>  [
            'base_uri'  =>  env('LOGS_SERVICE_BASE_URL', 'http://ccm-logs.local/'),
            'secret'  =>  env('LOGS_SERVICE_SECRET', 'qB20xFEIaSEW8ZAsEimIIn7mBA0x68LB0TcER1FE'),
        ],
        'monolith'   =>  [
            'base_uri'  =>  env('MONOLITH_SERVICE_BASE_URL', 'http://ccm-monolith.local'),
            'secret'  =>  env('MONOLITH_SERVICE_SECRET', 'qB20xFEIaSEW8ZAsEimIIn7mBA0x68LB0TcER1FE'),
        ],
    ];
