<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 24/12/2021
     * Time: 14:09
     */

    return
        [
            'debug' => env("APP_DEBUG", true),
            'env' => env("APP_ENV", 'development'),

            'portal_type' => ['customer', 'manager'],

            // user types for login
            'manager_portal' => 'manager',
            'customer_portal' => 'customer',
            'connection_erp_portal' => 'cnxnerp',

            'mgmnt_portal_url' => env("MGMNT_PORTAL_URL"),
            'customer_portal_url' => env("CUSTOMER_PORTAL_URL"),

            'app_url' => env('APP_URL', 'http://localhost'),

            // configuration for geolocation api
            'api_ip' => [
                'url' => env('GEO_LOCATION_URL'),
                'access_key' => env('GEO_LOCATION_KEY'),
            ],

            // Log event for QA testing
            'log_event_env' => env('LOG_EVENT',null),

//
//            'ical_url' => env("ICAL_URL"),
//            'page' => '1',
//
//            'O' => 'id',
//            'R' => '12',
//            'D' => '1',
//            'YES' => 'yes',
//            'NO' => 'no',
//            'ENABLED' => 'Enabled',
//            'DISABLED' => 'Disabled',
//
//            // database name
//            'business' => env('DB_BUSINESS_CONNECTION', 'business'),
//            'analytics' => env('DB_ANALYTICS_CONNECTION', 'analytics'),
//            'billing' => env('DB_BILLING_CONNECTION', 'billing'),
//            'sensitive' => env('DB_SENSITIVE_CONNECTION', 'sensitive'),
//
//
//            'ADDRESS_TYPE' => ['billing', 'address of use', 'company'],
//            'CREDIT_CARD_TYPE' => ['visa', 'mastercard', 'amex', 'discover'],
//            'STATUS' => ['0', '1'],
//
//            'criteria' => ['o' => 'id', 'r' => 100, 'd' => 1, 'fs' => [], 'disp' => []],
//            'export_formats' => ['csv', 'xlsx', 'xls'],
//            'report_file_formats' => ['pdf'],   // file formats for sending report as email
//            'account_status' => ['0', '1', '2'],
//            'payment_status' => ['0', '1', '2'],
//            'request_type' => ['GET', 'POST', 'PATCH', 'PUT', 'DELETE', 'COPY', 'HEAD', 'OPTIONS', 'LINK', 'UNLINK', 'PURGE', 'LOCK', 'UNLOCK', 'PROPFIND', 'VIEW'],
//            'content_type' => ['application/json', 'from', 'body'],
//            'response_type' => ['application/json', 'application/xml', 'text/html'],
//            'service_types' => ['application' => 'Application', 'infrastructure' => 'Infrastructure',],
//            'payment_method' => ['credit card', 'net terms'],
//            'sales_division' => ['ESG', 'PSG', 'BSG'],
//            'currency' => ['en_US' => '$', 'PKR' => 'Rs'],
//            'cost_projection_test_data' => [
//                [0 => 'Month', 1 => 'Cost', 2 => ['role' => 'style',],],
//                [0 => 'LAST MONTH (Feb 2019)', 1 => 269, 2 => '#006dc1',],
//                [0 => 'MONTH TO DATE (Mar 2019)', 1 => 208, 2 => '#6ad0f4',],
//                [0 => 'FORECAST (Mar 2019)', 1 => 308.9915484045, 2 => '#ccecfb'],
//                [0 => 'FORECAST (Apr 2019)', 1 => 371.6244769005, 2 => '#ccecfb'],
//                [0 => 'FORECAST (May 2019)', 1 => 203.1592059869, 2 => '#ccecfb'],
//                [0 => 'FORECAST (Jun 2019)', 1 => 913.9387826636, 2 => '#ccecfb'],
//                [0 => 'FORECAST (Jul 2019)', 1 => 277.8912624282, 2 => '#ccecfb'],
//                [0 => 'FORECAST (Aug 2019)', 1 => 237.4018149597, 2 => '#ccecfb']
//            ],
//
//            'from_name' => env('APP_FULL_NAME'),
//            'registered' => env('REGISTERED', null),
//            'app_full_name' => env('APP_FULL_NAME', null),
//            'app_full_name_r' => env('APP_FULL_NAME_R', null),
//            'app_short_name' => env('APP_SHORT_NAME', null),
//            'app_short_name_r' => env('APP_SHORT_NAME_R', null),
//
//            'test_email_to'  => env('TEST_EMAIL_TO', null),
//            'from_email' => env('FROM_EMAIL', 'no-reply@connection.com'),
//            'default_email' => env('DEFAULT_EMAIL', 'CCP.UAT@Connection.com'),
//
//            'ccp_desk_name' => env('CCP_DESK_Name', 'CCM Desk'),
//            'ccp_desk' => env('CCP_DESK', 'no-reply@connection.com'),
//
//            'operations_name' => env('OPERATIONS_NAME', 'Operations'),
//            'ccm_operations_email' => env('CCM_OPERATIONS_TEAM_EMAIL', 'no-reply@connection.com'),
//
//            'email_header_logo_path' => env('EMAIL_HEADER_LOGO_PATH', null),
//            'email_code_expiry' => env("EMAIL_CODE_EXPIRY", 24),
//            'customer_service_telephone_number' => env("CUSTOMER_SERVICE_TELEPHONE_NUMBER", ''),
//
//
//            // sendgrid configuration
//            'sendgrid' => [
//                'report_template_id' => 'd-d6ef0502f5544946996a81ca5ae8e32a',
//                'v3_api_key' => env('SEND_GRID_V3_API_KEY')
//            ],
//
//            // ESG account default invoice payer
//            'esg_invoice_payer' => env('ESG_DEFAULT_INVOICE_PAYER'),
//
//            // For AWS configuration
//            'aws_email'    => env('AWS_EMAIL'),
//
//            // service providers
//            'service_providers' => [
//                'microsoft_csp' => env('MICROSOFT_PROVIDER_ID', '14624698'),
//                'microsoft_perpetual_csp' => env('MICROSOFT_PERPETUAL_PROVIDER_ID', '14624698'),
//                'AWS' => '15249218',
//                'Adobe' => '12988453',
//                'Wasabi' => '20527060',
//                'TrendMicro' => '4511437',
//                'Acronis' => '8180232',
//            ],
//            /**
//             * Database Schema's Placeholders
//             */
//            'schema_business' => env('DB_BUSINESS_DATABASE', NULL),
//            'schema_sensitive' => env('DB_SENSITIVE_DATABASE', NULL),
//            'schema_analytics' => env('DB_ANALYTICS_DATABASE', NULL),
//            'schema_billing' => env('DB_BILLING_DATABASE', NULL),
//            'schema_audit' => env('DB_AUDIT_DATABASE', NULL),
//            'schema_reporting' => env('DB_REPORTING_DATABASE', NULL),
//            'schema_cloud' => env('DB_CLOUD_DATABASE', NULL),
//
//            'encrypt_decrypt_key' => env('ENCRYPT_DECRYPT_KEY','SafnGWw49z9WbpMCLzpQGtENgj5BA3sG')
        ];
