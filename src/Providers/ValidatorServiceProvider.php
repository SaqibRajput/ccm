<?php

namespace CCM\Leads\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;

class ValidatorServiceProvider extends ServiceProvider
{
    /**
     * custom validation the application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('alpha_num_dash_space_line_breaks', function($attribute, $value, $parameters, $validator) {
            if( preg_match('/^[A-Za-z0-9 \n -]+$/', $value) )
                return true;

            return false;
        });

        Validator::extend('alpha_num_space_special_character', function($attribute, $value, $parameters, $validator) {
            if( preg_match('/^[a-zA-Z0-9\s\W\w]+$/', $value) )
                return true;

            return false;
        });

        Validator::extend('alpha_num_dash_space', function($attribute, $value, $parameters, $validator) {
            if( preg_match('/^[A-Za-z0-9 -]+$/', $value) )
                return true;

            return false;
        });

        Validator::extend('full_name', function($attribute, $value, $parameters, $validator) {
            if( preg_match('/^((\b[a-zA-Z]{1,40}\b)\s*){2,}$/', $value) )
                return true;

            return false;
        });

        Validator::extend('only_numeric', function($attribute, $value, $parameters, $validator) {
            if( preg_match(' /^[0-9]+$/', $value) )
                return true;

            return false;
        });

        Validator::extend('non_zeros', function($attribute, $value, $parameters, $validator) {
            if( preg_match(' /^[1-9]\d*$/', $value) )
                return true;

            return false;
        });

        Validator::extend('binary_with_colon', function($attribute, $value, $parameters, $validator) {
            if( preg_match('/^[01]:[01]$/', $value) )
                return true;

            return false;
        });

        Validator::extend('zip_code', function($attribute, $value, $parameters, $validator) {

            if( preg_match('/^[0-9]{5}$/', $value) )
            {
                return true;
            } elseif ( preg_match('/^[0-9]{5}\ [0-9]{4}$/', $value) ) {
                return true;
            }

            return false;
        });

        Validator::extend('city', function($attribute, $value, $parameters, $validator) {
            if( preg_match('/^[a-zA-Z0-9\s]+$/', $value) )
                return true;

            return false;
        });

        Validator::extend('phone', function($attribute, $value, $parameters, $validator) {
            if( preg_match("/^\+1\-[0-9]{3}\-[0-9]{3}\-[0-9]{4}$/", $value) )
                return true;

            return false;
        });

        Validator::extend('comma_separated_int', function($attribute, $value, $parameters, $validator) {
            if( preg_match("/^(\d+(,\d+)*)?$/", $value) )
                return true;

            return false;
        });

        Validator::extend('only_numbers_letters', function($attribute, $value, $parameters, $validator) {
            if( preg_match('/^([0-9]+[a-zA-Z]+|[a-zA-Z]+[0-9]+)[0-9a-zA-Z]*$/', $value) )
                return true;

            return false;
        });

        Validator::extend('credir_card_number', function($attribute, $value, $parameters, $validator) {
            $cardtype = array(
                "visa"       => "/^4[0-9]{12}(?:[0-9]{3})?$/",
                "mastercard" => "/^5[1-5][0-9]{14}$/",
                "amex"       => "/^3[47][0-9]{13}$/",
                "discover"   => "/^6(?:011|5[0-9]{2})[0-9]{12}$/",
            );

            if(isset($cardtype[$parameters[0]])){
                if (preg_match($cardtype[$parameters[0]],$value)){
                    return true;
                }
            }

            return false;
        });

        Validator::extend('credir_card_cvv', function($attribute, $value, $parameters, $validator) {
            if($parameters[0] == 'amex' && preg_match('/^[0-9]{4}$/', $value)){
                return true;
            }
            elseif ($parameters[0] != 'amex' && preg_match('/^[0-9]{3}$/', $value)){
                return true;
            }
            return false;
        });

        Validator::extend('emails', function($attribute, $value, $parameters, $validator) {
            $emails = explode(',', $value);
            $rules = [
                'email' => 'email',
            ];
            foreach ($emails as $email) {
                $data = [
                    'email' => $email
                ];
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return false;
                }
            }
            return true;
        });

        Validator::extend('isJson', function($attribute, $value, $parameters, $validator) {

            if(is_string($value) && is_array(json_decode($value, true)) && (json_last_error() == JSON_ERROR_NONE)){
                return true;
            }
            return false;
        });

        Validator::extend('grid_format', function($attribute, $value, $parameters, $validator) {
            if( preg_match("/^[a-zA-Z0-9-]+$/", $value) )
                return true;

            return false;
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
