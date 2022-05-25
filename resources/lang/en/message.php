<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 24/01/2022
     * Time: 15:49
     */

    return [

        'validation' => [

            // default validation messages
            'required' => '----- (Required) : (:field) : (:attribute) -----',
            'email' => '----- (Email) : (:field) : (:attribute) -----',
            'unique' => '----- (Unique) : (:field) : (:attribute) -----',
            'in' => '----- (In) : (:field) : (:attribute) : (:option) -----',
            'exists' => '----- (Exists) : (:field) : (:attribute) -----',
            'numeric' => '----- (Numeric) : (:field) : (:attribute) -----',
            'min' => '----- (Min) : (:field) : (:attribute) -----',
            'max' => '----- (Max) : (:field) : (:attribute) -----',
            'regex' => '----- (Regex) : (:field) : (:attribute) -----',

            // custom validation messages
            'alpha_num_dash_space_line_breaks' => '----- (alpha_num_dash_space_line_breaks) : (:field) : (:attribute) -----',
            'alpha_num_space_special_character' => '----- (alpha_num_space_special_character) : (:field) : (:attribute) -----',
            'alpha_num_dash_space' => '----- (alpha_num_dash_space) : (:field) : (:attribute) -----',
            'full_name' => '----- (full_name) : (:field) : (:attribute) -----',
            'only_numeric' => '----- (only_numeric) : (:field) : (:attribute) -----',
            'non_zeros' => '----- (non_zeros) : (:field) : (:attribute) -----',
            'binary_with_colon' => '----- (binary_with_colon) : (:field) : (:attribute) -----',
            'zip_code' => '----- (zip_code) : (:field) : (:attribute) -----',
            'city' => '----- (city) : (:field) : (:attribute) -----',
            'phone' => '----- (phone) : (:field) : (:attribute) -----',
            'comma_separated_int' => '----- (comma_separated_int) : (:field) : (:attribute) -----',
            'only_numbers_letters' => '----- (only_numbers_letters) : (:field) : (:attribute) -----',
            'credir_card_number' => '----- (credir_card_number) : (:field) : (:attribute) -----',
            'credir_card_cvv' => '----- (credir_card_cvv) : (:field) : (:attribute) -----',
            'emails' => '----- (emails) : (:field) : (:attribute) -----',
            'isJson' => '----- (isJson) : (:field) : (:attribute) -----',
            'grid_format' => '----- (grid_format) : (:field) : (:attribute) -----',

        ],
        'service' => [
            'failed' => 'failed internal service call for :serviceName, process depends on this service so we cannot continue without this.',
        ],
        'error' => [
            'db' => 'db db db db db',
            'exception' => 'exception exception exception exception',
            'throwable' => 'throwable throwable throwable throwable.',
            'curl' => 'curl curl curl curl curl',
        ],
        'working_on_it' => "Need to update message, its just temporary fixed"
    ];
