<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 28/02/2022
     * Time: 1:43 PM
     */

    namespace CCM\Leads\Traits;

    use validator;
    use Illuminate\Http\Request;

    trait CustomValidation
    {
        public static function validation(Request $request, $rule, $returnData = [], $messages = [], $attribute = [])
        {
            lumenLog('CustomValidation::execute');

            try
            {
                $validator = validator::make($request->all(), $rule, self::customMessages($messages), $attribute);

                if ($validator->fails())
                {
                    return createResponseData(self::STATUS_CODE_VALIDATION_FAILED, self::RESPONSE_FAILED, $validator->errors());
                }

                return createResponseData(self::STATUS_CODE_SUCCESS, self::RESPONSE_SUCCESS);

            }
            catch(\Exception $ex)
            {
                lumenLog('Validation Exception start');
                lumenLog($ex->getTrace());
                lumenLog( $ex->getMessage() .' - '. $ex->getLine());
                lumenLog('Validation Exception end');
                return createResponseData(self::STATUS_CODE_VALIDATION_FAILED, self::RESPONSE_FAILED, trans('main::message.error.exception'), $returnData);
            }
            catch (\Throwable $t)
            {
                lumenLog('Validation Throwable start');
                lumenLog($t->getTrace());
                lumenLog( $t->getMessage() .' - '. $t->getLine());
                lumenLog('Validation Throwable end');
                return createResponseData(self::STATUS_CODE_VALIDATION_FAILED, self::RESPONSE_FAILED, trans('main::message.error.throwable'), $returnData);
            }
        }

        public function customMessages($overwrite = [])
        {
            $result = [
                'required' => trans('main::message.validation.required'),
                'email' => trans('main::message.validation.email'),
                'unique' => trans('main::message.validation.unique'),
                'in' => trans('main::message.validation.in'),
                'exists' => trans('main::message.validation.exists'),
                'numeric' => trans('main::message.validation.numeric'),
                'regex' => trans('main::message.validation.regex'),
                'min' => trans('main::message.validation.min'),

                'alpha_num_dash_space_line_breaks' => trans('main::message.validation.alpha_num_dash_space_line_breaks'),
                'alpha_num_space_special_character' => trans('main::message.validation.alpha_num_space_special_character'),
                'alpha_num_dash_space' => trans('main::message.validation.alpha_num_dash_space'),
                'full_name' => trans('main::message.validation.full_name'),
                'only_numeric' => trans('main::message.validation.only_numeric'),
                'non_zeros' => trans('main::message.validation.non_zeros'),
                'binary_with_colon' => trans('main::message.validation.binary_with_colon'),
                'zip_code' => trans('main::message.validation.zip_code'),
                'city' => trans('main::message.validation.city'),
                'phone' => trans('main::message.validation.phone'),
                'comma_separated_int' => trans('main::message.validation.comma_separated_int'),
                'only_numbers_letters' => trans('main::message.validation.only_numbers_letters'),
                'credir_card_number' => trans('main::message.validation.credir_card_number'),
                'credir_card_cvv' => trans('main::message.validation.credir_card_cvv'),
                'emails' => trans('main::message.validation.emails'),
                'isJson' => trans('main::message.validation.isJson'),
                'grid_format' => trans('main::message.validation.grid_format'),
            ];

            if(!empty($overwrite))
            {
                $result = array_merge($result, $overwrite);
            }

            return $result;
        }
    }
