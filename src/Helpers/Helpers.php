<?php

    use GuzzleHttp\Client;
    use Illuminate\Http\Request;

    if (! function_exists('createResponseData'))
    {
        function createResponseData($code = 200, $success = true, $message = '', $data = [], $pagination = false, $request = false, $skip = false)
        {
            $response = [];
            if (!$skip)
            {
                if (gettype($message) == 'object')
                {
                    $message = collect($message->getMessages())->map(function ($item) {
                        return collect($item)->map(function ($item) {
                            $item = preg_replace("/(\.|,)$/", "", $item);
                            $item = ucfirst(str_replace(' id ', ' ID ', $item));
                            $item = preg_replace_callback('/[.!?].*?\w/', function ($matches) { return strtoupper($matches[0]); }, $item);

                            return preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) { return strtolower($matches[0]); }, $item);
                        })->toArray();
                    });
                }
                else if (gettype($message) == 'array')
                {
                    $new_message = (object)[];
                    foreach ($message as $index => $item)
                    {
                        foreach ($item as $key => $value)
                        {
                            $value                   = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $value))));
                            $value                   = preg_replace_callback('/[.!?].*?\w/', function ($matches) { return strtoupper($matches[0]); }, $value);
                            $new_message->{$index}[] = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) { return strtolower($matches[0]); }, $value);
                        }
                    }
                    $message = $new_message;
                }
                else
                {
                    if (is_array($message))
                    {
                        $messages = $message;
                        $message  = [];
                        foreach ($messages as $msg)
                        {
                            if (is_array($msg))
                            {
                                $msgs = $msg;
                                foreach ($msgs as $m)
                                {
                                    $message = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $m))));
                                    $message = preg_replace_callback('/[.!?].*?\w/', function ($matches) { return strtoupper($matches[0]); }, $message);
                                    $message = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) { return strtolower($matches[0]); }, $message);
                                }
                            }
                            else
                            {
                                $message = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $msg))));
                                $message = preg_replace_callback('/[.!?].*?\w/', function ($matches) {
                                    return strtoupper($matches[0]);
                                }, $message);
                                $message = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) {
                                    return strtolower($matches[0]);
                                }, $message);
                            }
                        }
                    }
                    else
                    {
                        $message = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $message))));
                        $message = preg_replace_callback('/[.!?].*?\w/', function ($matches) {
                            return strtoupper($matches[0]);
                        }, $message);
                        $message = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) {
                            return strtolower($matches[0]);
                        }, $message);
                    }
                }
            }
            $response['status_code'] = $code;
            $response['success']     = $success;
            $response['message']     = $message;
            if ($pagination)
            {
                $response['pages'] = $pagination;
            }
            $response['data'] = $data;

            return $response;
        }
    }

    if (! function_exists('lumenLog'))
    {
        function lumenLog($message)
        {
            \Log::info($message);
        }
    }

    if (!function_exists('curlRequest'))
    {
        function curlRequest($method, $endPoint, $params = [], $timeOut = 0)
        {
            $response = null;

            try
            {
                $timeOutArray = [
                    'timeout' => $timeOut,
                    'connect_timeout' => $timeOut
                ];

                $client   = new Client($timeOutArray);
                $response = $client->request($method, $endPoint, $params);

            }
            catch (\Exception $e)
            {
                lumenLog('Exception: curlRequest : Start');
                lumenLog('$timeOut: '.$timeOut);
                lumenLog('$method: '.$method);
                lumenLog('$endPoint: '.$endPoint);
                lumenLog('$params: '.json_encode($params));
                lumenLog($e->getLine().' - '.$e->getMessage());
                lumenLog('Exception: curlRequest : End');
            }

            return $response;
        }
    }

    use Illuminate\Support\Str;

    if (!function_exists('uniqueCode'))
    {
        function uniqueCode($limit = "25", $isString = true)
        {
            $result = '';
            if($isString)
            {
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            }
            else {
                $characters = '0123456789';
            }

            for($a = 1 ; $a <= $limit ; $a++) {
                $result .= $characters[rand(0, strlen($characters)-1)];
            }

            return $result;
        }
    }

    /**
     * Extract Domain from email
     *
     * @param (string) $email
     * @return (string) $domain
     */
    if (!function_exists('extractDomain'))
    {
        function extractDomain($email)
        {

            $domain = strstr($email,'@');
            if ($domain) {
                $domain = str_replace('@', '', $domain);
            }

            return $domain;
        }
    }

    use Carbon\Carbon;

    // it should be removed from helpers
    // need to call companies service to get setting.
    if (! function_exists('updateVerificationExpiryFromSettings'))
    {
        function updateVerificationExpiryFromSettings()
        {
            return Carbon::now()->addHours(config('main.email_code_expiry'));

            die(' --- updateVerificationExpiryFromSettings --- ');
            $email_code_expiry = Setting::where([
                'key'               => 'email_code_expiry',
                'portal_type'       => 'customer',
            ])->select('key', 'value', 'portal_type')->first();

            if(optional($email_code_expiry)->value){
                if(intval(optional($email_code_expiry)->value) > 0)
                    return Carbon::now()->addHours(optional($email_code_expiry)->value);
            }

        }
    }

    /**
     * Base 64 encode a string with url encode
     * @param $str
     * @return string
     */
    if (! function_exists('base64urlEncode'))
    {
        function base64urlEncode($str)
        {
            return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
        }
    }

    /**
     * Base 64 decode a string with url decode
     * @param $str
     * @return string
     */
    if (! function_exists('base64urlDecode'))
    {
        function base64urlDecode($str)
        {
            return base64_decode(str_pad(strtr($str, '-_', '+/'), strlen($str) % 4, '=', STR_PAD_RIGHT));
        }
    }

    /**
     * validate json
     * @param $str
     * @return boolean
     */
    if (! function_exists('isJson'))
    {
        function isJson($string)
        {
            return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
        }
    }
