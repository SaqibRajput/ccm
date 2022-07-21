<?php

    use GuzzleHttp\Client;
    use Illuminate\Http\Request;
    use Illuminate\Encryption\Encrypter;
    use Illuminate\Support\Str;
    use Carbon\Carbon;
    use Monolog\Handler\RotatingFileHandler;
    use Monolog\Logger;
    use CCM\Leads\Jobs\SendEmail;
    use GuzzleHttp\Exception\ClientException;
    use Component\LoggingComponent\App\RequestLogReader;
    use App\Jobs\SyncAddons;
    use App\Jobs\SyncCategories;
    use App\Jobs\SyncProviders;
    use App\Jobs\SyncService;
    use Illuminate\Support\Arr;

    /**
    * Create API response
    * @param $code
    * @param $success
    * @param $message
    * @param $data
    * @param $pagination
    * @param $request
    * @param $skip
    * @return array
    **/
    if (! function_exists('createResponseData')) {
        function createResponseData($code, $success, $message = '', $data = [], $pagination, Illuminate\Http\Request $request, $skip = false)
        {
            $response = [];
            if (!$skip) {
                if (gettype($message) == 'object') {
                    $message = collect($message->getMessages())->map(function ($item) {
                        return collect($item)->map(function ($item) {
                            $item = preg_replace("/(\.|,)$/", "", $item);
                            $item = ucfirst(str_replace(' id ', ' ID ', $item));
                            $item = preg_replace_callback('/[.!?].*?\w/', function ($matches) {
                                return strtoupper($matches[0]);
                            }, $item);
                            return preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) {
                                return strtolower($matches[0]);
                            }, $item);
                        })->toArray();
                    });
                } elseif (gettype($message) == 'array') {
                    $new_message = (object)[];
                    foreach ($message as $index=>$item) {
                        foreach ($item as $key=>$value) {
                            $value = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $value))));
                            $value = preg_replace_callback('/[.!?].*?\w/', function ($matches) {
                                return strtoupper($matches[0]);
                            }, $value);
                            $new_message->{$index}[] = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) {
                                return strtolower($matches[0]);
                            }, $value);
                        }
                    }
                    $message = $new_message;
                } else {
                    if (is_array($message)) {
                        $messages = $message;
                        $message = [];
                        foreach ($messages as $msg) {
                            if (is_array($msg)) {
                                $msgs = $msg;
                                foreach ($msgs as $m) {
                                    $message = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $m))));
                                    $message = preg_replace_callback('/[.!?].*?\w/', function ($matches) {
                                        return strtoupper($matches[0]);
                                    }, $message);
                                    $message = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) {
                                        return strtolower($matches[0]);
                                    }, $message);
                                }
                            } else {
                                $message = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $msg))));
                                $message = preg_replace_callback('/[.!?].*?\w/', function ($matches) {
                                    return strtoupper($matches[0]);
                                }, $message);
                                $message = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) {
                                    return strtolower($matches[0]);
                                }, $message);
                            }
                        }
                    } else {
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
            $response['success'] = $success;
            $response['message'] = $message;
        
            if ($pagination) {
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

    // if (!function_exists('curlRequest'))
    // {
    //     function curlRequest($method, $endPoint, $params = [], $timeOut = 0)
    //     {
    //         $response = null;

    //         try
    //         {
    //             $timeOutArray = [
    //                 'timeout' => $timeOut,
    //                 'connect_timeout' => $timeOut
    //             ];

    //             $client   = new Client($timeOutArray);
    //             $response = $client->request($method, $endPoint, $params);

    //         }
    //         catch (\Exception $e)
    //         {
    //             lumenLog('Exception: curlRequest : Start');
    //             lumenLog('$timeOut: '.$timeOut);
    //             lumenLog('$method: '.$method);
    //             lumenLog('$endPoint: '.$endPoint);
    //             lumenLog('$params: '.json_encode($params));
    //             lumenLog($e->getLine().' - '.$e->getMessage());
    //             lumenLog('Exception: curlRequest : End');
    //         }

    //         return $response;
    //     }
    // }

  

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

    /**
     * Remove double strings
     * @param (string) $str
     * @return (string) $str
     */
    if (! function_exists('removeDoubleSpace'))
    {
        function removeDoubleSpace($str)
        {
            $str = str_replace('  ', ' ', $str);
            if (strpos($str, '  ') !== false)
            {
                return removeDoubleSpace($str);
            }

            return $str;
        }
    }

    /*
    * Generates encrypted value
    *
    * @param string $string
    * @return string $text
    */
    if (!function_exists('encryptString'))
    {

        function encryptString($string)
        {
            try
            {
                $customKey      = config('main.encrypt_decrypt_key'); //32 character long
                $newEncrypter   = new Encrypter($customKey,'AES-256-CBC');
                $encrypted      = $newEncrypter->encrypt($string);

            } catch (DecryptException $e)
            {
                $encrypted = $string;
            }

            return $encrypted;
        }
    }

    /*
    * Generates decrypted value
    *
    * @param string $string
    * @return string $text
    */
    if (!function_exists('decryptString'))
    {
        function decryptString($encryptedValue)
        {
            $decrypted = '';
            try
            {
                $customKey      = config('main.encrypt_decrypt_key'); //32 character long
                $newDecrypted   = new Encrypter($customKey,'AES-256-CBC');
                $decrypted      = $newDecrypted->decrypt($encryptedValue);

            } catch (DecryptException $e)
            {
                $decrypted = $encryptedValue;
            }
            return $decrypted;
        }
    }

/**
 * Create Grid API response
 *  @param (integer) $code
 *  @param (bool) $success
 *  @param (bool) $message
 *  @param (array) $embedded
 *  @param (array) $entities
 *  @param (array) $pagination
 **/
function createGridResponseData($code, $success, $message = '',$embedded = [], $entities = [],$pagination, Illuminate\Http\Request $request){

    $response = [];

    $response['status_code'] = $code;
    $response['success'] = $success;
    $response['message'] = $message;

    if($pagination){
        $response['pages'] = $pagination;
    }

    $response['embedded'] = $embedded;
    $response['entities'] = $entities;

    return $response;
}

/**
 * Verifies if given parameter is json, incase of not json then aborts
 *
 * @param (string) $json
 * @return
 */
function isJsonRequestBody($jsonRequestBody)
{
    if (!empty($jsonRequestBody) && empty(json_decode($jsonRequestBody))) { // if received json from raw body is not valid
        return abort(400, 'Invalid json');
    }

}


/**
 * Base 64 encode a string with url encode
 * @param $str
 * @return string
 */
function base64urlEncode($str) {
    return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
}

/**
 * Base 64 decode a string with url encode
 * @param $str
 * @return string
 */
function base64urlDecode($str) {
    return base64_decode(str_pad(strtr($str, '-_', '+/'), strlen($str) % 4, '=', STR_PAD_RIGHT));
}

/*
 * Check if user have permission
 * @param (string) $permission
 * @param (string) $accessType
 * @return (bool)
 */
function can($permission, $accessType) {

    $userPermissions = app('request')->user()->getAllPermissionsFormAllRoles();
    if ($userPermissions->get($permission) != $accessType && $userPermissions->get($permission) != "full_access") {
        return false;
    }

    return true;
}

/**
 * Prepare or Repair criteria (to be used by fetch and export methods)
 *
 * @param array $criteria
 * @return array|mixed
 */
function prepareCriteria ($criteria = []) {
    $defaultCriteria = config('main.criteria');

    if (!empty($criteria)) {
        switch (gettype($criteria)) {
            case 'array':
                $criteria = array_merge($defaultCriteria, (array) $criteria);

                if (!empty($criteria['fs'])){
                    $criteria['fs'] = array_filter($criteria['fs']);
                }

                if (!empty($criteria['disp'])){
                    $criteria['disp'] = array_filter($criteria['disp']);
                }

                if (empty($criteria['r']) || (int) $criteria['r'] <= 0){
                    $criteria['r'] = config('main.criteria.r');
                } else {
                    $criteria['r'] = (int) $criteria['r'];
                }

                if (empty($criteria['o'])){
                    $criteria['o'] = config('main.criteria.o');
                }

                if (isset($criteria['d']) && ((int) $criteria['d'] !== 1 && (int) $criteria['d'] !== 0)){
                    $criteria['d'] = config('main.criteria.d');
                }

                break;
            case 'string':
                $criteria = json_decode($criteria, true);

                if (empty($criteria['o'])){
                    $criteria['o'] = config('main.criteria.o');
                }

                if (empty($criteria['r']) || (int) $criteria['r'] <= 0){
                    $criteria['r'] = config('main.criteria.r');
                } else {
                    $criteria['r'] = (int) $criteria['r'];
                }

                if (isset($criteria['d']) && ((int) $criteria['d'] !== 1 && (int) $criteria['d'] !== 0)){
                    $criteria['d'] = config('main.criteria.d');
                }

                if (!empty($criteria['fs'])) {
                    $criteria['fs'] = array_filter($criteria['fs']);
                } else {
                    $criteria['fs'] = config('main.criteria.fs');
                }

                if (!empty($criteria['disp'])) {
                    $criteria['disp'] = array_filter($criteria['disp']);
                } else {
                    $criteria['disp'] = config('main.criteria.disp');
                }

                break;
            default:
                $criteria = $defaultCriteria;
                break;
        }
    } else {
        $criteria = $defaultCriteria;
    }

    // url-Decoding values of 'fs' key in Criteria recursively
    if (isset($criteria['fs']) && !empty($criteria['fs'])) {
        foreach ($criteria['fs'] as $key => $fs) {
            if (is_array($fs)) {
                foreach ($fs as $i => $f) {
                    $criteria['fs'][$key][$i] = rawurldecode($f);
                }
            } else {
                $criteria['fs'][$key] = rawurldecode($fs);
            }
        }
    }

    return json_decode(json_encode($criteria));
} 
 
 
 
 

/**
 * Function to get Class name
 * @return string
*/  
function getAbsClassName($object) {
    $classNameWithNamespace = get_class($object);

    if(substr($classNameWithNamespace, strrpos($classNameWithNamespace, '\\')+1) == "SendEmailCCMUserController") {
        return "Send Emails";
    }

    return substr($classNameWithNamespace, strrpos($classNameWithNamespace, '\\')+1);
}




/**
 * Function that returns clean json format
 * @return string
*/  
function jsonClean($json){
    $str_response = mb_convert_encoding($json, 'utf-8', 'auto');

    for ($i = 0; $i <= 31; ++$i) {
        $str_response = str_replace(chr($i), "", $str_response);
    }
    $str_response = str_replace(chr(127), "", $str_response);

    if (0 === strpos(bin2hex($str_response), 'efbbbf')) {
        $str_response = substr($str_response, 3);
    }

    return $str_response;
}


/**
 * Create User Specific Logs
 *
 * @param $method
 * @param $endPoint
 * @param $data
 * @param $headers
 * @param Request $request
 */
function setApiLog($method, $endPoint, $data, $headers, Request $request)
{
    $auth = $request->user();

    $user_id = !empty($auth->id) ? $auth->id : 0;
    $first_name = !empty($auth->first_name) ? $auth->first_name : 'System';
    $last_name = !empty($auth->last_name) ? $auth->last_name : '';

    $data['Request_headers'] = $headers;

    $handler = new RotatingFileHandler(storage_path() . '/logs/' . $user_id . '.log', 0, Logger::INFO, true, 0664);
    $logger = new Logger($first_name . " " . $last_name);

    $handler->setFilenameFormat('{date}_{filename}', 'Y_m_d');
    $logger->pushHandler($handler);
    $array = [$method . ' - ' . $endPoint, json_encode($data)];

    // $logger->addError('API-Response', $array);
    $logger->error('API-Response', $array);
}


   /**
     * Function that trim values   
     * @param $data
     * @param $value
     * @return mixed
     */
    function handleDefaultValue($data, $value)
    {
        $defualt_text = '';
        if ( false !== strpos( $value, '[' ) ) {
            $defualt_text = ltrim(rtrim(strstr($value, '[', 0), ']'), '[');
            $value = strstr($value, '[', 1);
        }

        try
        {
            $final_value = arr::get($data, $value, $value).$defualt_text;
        }
        catch(Exception $e) {
            $final_value = $value;
        }

        return $final_value;
    }


/**
* Function that trim values   
* @param $data
* @param $value
* @return mixed
*/
function attrite($request, $data, $is_response = false){
        if (is_array($request))
        {
            foreach ($request as $key => $value)
            {
                if (is_array($value))
                {
                    $request[$key] = attrite($value, $data);
                }
                else
                {
                    $translated = handleDefaultValue($data, $value);
                    if ($is_response && $translated == $value)
                    {
                        // Raise error -
                        throw new InvalidResponseException('Invalid Response Exception.');
                    }
                    $request[$key] = $translated;
                }
            }
        } 
    return $request;
}    
 

 

/**
* Method to change the First word of status
*
* @param string $status
* @return string
*/

if (! function_exists('setOrderState'))
{
    function setOrderState($status = NULL){
        $state = false;
        if(strtolower($status) === strtolower('Fulfilled'))
            $state = 'Ready';
        if(strtolower($status) === strtolower('Pending'))
            $state = 'System Processing';
        if(strtolower($status) === strtolower('Cancelled'))
            $state = 'Ready';
        \Log::info("*setOrderState* : (status: '".$status."') state => '".$state."'");
        return $state;
    }
}


/**
* Method to change the First word of status
*
* @param string $status
* @return string
*/
if (! function_exists('setOrderLineItemState'))
{
    function setOrderLineItemState($status = NULL){
        $state = false;
        if(strtolower($status) === strtolower('Fulfilled'))
            $state = 'Ready';
        if(strtolower($status) === strtolower('Pending'))
            $state = 'System Processing';
        if(strtolower($status) === strtolower('Cancelled'))
            $state = 'Ready';
        \Log::info("*setOrderLineItemState* : (status: '".$status."') state => '".$state."'");
        return $state;
    }
}

/**
* Method to return Order state based on integration type and status
*
* @param string $status
* @param string $integration_type
* @return string
*/

if (! function_exists('setTenantState'))
{
    function setTenantState($status = NULL, $integration_type = NULL){
        $state = false;
        if(strtolower($status) === strtolower('Pending') && strtolower($integration_type) === strtolower('automate'))
            $state = 'System Processing';
        else if(strtolower($status) === strtolower('Pending') && strtolower($integration_type) === strtolower('manual'))
            $state = 'Waiting for Input';
        else if(strtolower($status) === strtolower('Active') && strtolower($integration_type) === strtolower('automate'))
            $state = 'Ready';
        else if(strtolower($status) === strtolower('Active') && strtolower($integration_type) === strtolower('manual'))
            $state = 'Ready';
        \Log::info("*setTenantState* : (status: '".$status."', integration_type: '".$integration_type."') state => '".$state."'");
        return $state;
    }
}

/**
* Method to return subscription state based on integration_method and status
*
* @param string $status
* @param string $integration_method
* @return string
*/

if (! function_exists('setSubscriptionState'))
{
    function setSubscriptionState($status = NULL, $integration_method = NULL){

        $forReadyStateStatuses = [1,2,3,4,5,6];

        $state = false;

        // for status (active, terminated, expired, cancelled, freeze) and integration may be manual or auto
        if(in_array($status, $forReadyStateStatuses))
            $state = 'Ready';

        // pending/auto
        else if($status === 0 && (strtolower($integration_method) === strtolower('Automatic') || strtolower($integration_method) === strtolower('automate')))
            $state = 'System Processing';
        // pending/manual
        else if($status === 0 && strtolower($integration_method) === strtolower('manual'))
            $state = 'Waiting for Input';
/*
        // suspended/manual
        else if($status === 2 && strtolower($integration_method) === strtolower('manual'))
            $state = 'Suspended';
        // suspended/auto
        else if($status === 2 && (strtolower($integration_method) === strtolower('Automatic') || strtolower($integration_method) === strtolower('automate')))
            $state = 'Ready';
*/
        /*
        // currently this never be checked because it is already in array of ready state so can be removed from here
        // freeze/manual
        else if($status === 6 && strtolower($integration_method) === strtolower('manual'))
            $state = 'Ready'; // this need to be Freezed but in sheet it is ready
        // freeze/auto
        else if($status === 6 && (strtolower($integration_method) === strtolower('Automatic') || strtolower($integration_method) === strtolower('automate')))
            $state = 'Ready';
        */

        \Log::info("*setSubscriptionState* : (status: '".$status."', integration_method: '".$integration_method."') state => '".$state."'");

        return $state;
    }
}

 


/**
     * Compute the start and end date of some fixed o relative quarter in a specific year.
     * @param mixed $quarter  Integer from 1 to 4 or relative string value:
     *                        'this', 'current', 'previous', 'first' or 'last'.
     *                        'this' is equivalent to 'current'. Any other value
     *                        will be ignored and instead current quarter will be used.
     *                        Default value 'current'. Particulary, 'previous' value
     *                        only make sense with current year so if you use it with
     *                        other year like: get_dates_of_quarter('previous', 1990)
     *                        the year will be ignored and instead the current year
     *                        will be used.
     * @param int $year       Year of the quarter. Any wrong value will be ignored and
     *                        instead the current year will be used.
     *                        Default value null (current year).
     * @param string $format  String to format returned dates
     * @return array          Array with two elements (keys): start and end date.
     */
    if (! function_exists('get_dates_of_quarter'))
    {
        function get_dates_of_quarter($quarter = 'current', $year = null, $format = 'Y-m-d H:i:s')
        {
            if ( !is_int($year) ) {
                $year = (new DateTime)->format('Y');
            }
            $current_quarter = ceil((new DateTime)->format('n') / 3);
            switch (  strtolower($quarter) ) {
                case 'this':
                case 'current':
                    $quarter = ceil((new DateTime)->format('n') / 3);
                    break;

                case 'previous':
                    $year = (new DateTime)->format('Y');
                    if ($current_quarter == 1) {
                        $quarter = 4;
                        $year--;
                    } else {
                        $quarter =  $current_quarter - 1;
                    }
                    break;

                case 'first':
                    $quarter = 1;
                    break;

                case 'last':
                    $quarter = 4;
                    break;

                default:
                    $quarter = (!is_int($quarter) || $quarter < 1 || $quarter > 4) ? $current_quarter : $quarter;
                    break;
            }
            if ( $quarter === 'this' ) {
                $quarter = ceil((new DateTime)->format('n') / 3);
            }
            $start = new DateTime($year.'-'.(3*$quarter-2).'-1 00:00:00');
            $end = new DateTime($year.'-'.(3*$quarter).'-'.($quarter == 1 || $quarter == 4 ? 31 : 30) .' 23:59:59');

            return array(
                'start' => $format ? $start->format($format) : $start,
                'end' => $format ? $end->format($format) : $end,
            );
        }
    }

    /**
     * Method that replaces subject
     * @param $subject
     * @return $string 
     */
    
    if (! function_exists('getOnlySubject'))
    {
        function getOnlySubject($subject)
        {
            $subject = trim($subject);
            if($subject !== ""){
                $subject = trim(str_replace(config('main.app_full_name_r'), '', $subject));
                $subject = trim(str_replace(config('main.app_full_name'), '', $subject));
                $subject = trim(str_replace(config('main.app_short_name'), '', $subject));
                $subject = trim(str_replace(config('main.registered'), '', $subject));
                $subject = str_replace('–', '', $subject);
                $subject = trim(trim(trim(trim($subject), '-'), '_'));
                $subject = preg_replace('~(?<!\S)-|-(?!\S)~', '', $subject);
            }
            return $subject;
        }
    }

    /**
     * Method that replaces delimiters from email string
     * @param $email_string
     * @return array 
     */
    if (! function_exists('filterEmails')){
        function filterEmails($email_string) {
            $delimiters = array(",", ";");
            $email_string_ready = str_replace($delimiters, $delimiters[0], $email_string);
            return array_unique(array_filter(array_map('trim', explode($delimiters[0], $email_string_ready))));
        }
    }


     /**
     * Method that appends mentioned delimiters
     * @param $email_string
     * @return string 
     */

    if (! function_exists('filterEmailString')){
        function filterEmailString($email_string) {
            $delimiter = ';';
            if(strpos($email_string, ','))
                $delimiter = ',';
            return implode($delimiter, filterEmails($email_string));
        }
    }

     /**
    * Remove double strings
    * @param (string) $str
    * @return (string) $str
    */
    function removeDoubleSpace($str)
    {
        $str = str_replace('  ', ' ', $str);
        if (strpos($str, '  ') !== false) {
            return removeDoubleSpace($str);
        }

        return $str;
    }

    /**
    * Remove Provider Custom Message
    * @param  $provider_custom_message
    * @return (string) $str
    */
    function cleanProviderCustomMessage($provider_custom_message = null){
        if($provider_custom_message != null && $provider_custom_message != ''){
            $provider_custom_message = trim($provider_custom_message);
            $pattern = "/<p[^>]*>[\s|&nbsp;|\<br\>|]*<\/p>$/";
            while(preg_match($pattern, $provider_custom_message, $matched)){
                $provider_custom_message = trim($provider_custom_message);
                $provider_custom_message = trim(preg_replace($pattern, '', $provider_custom_message));
            }
            return '<b style="text-decoration:underline">Provider Information</b><br><span>' . $provider_custom_message . '</span>';
        }
        return '';
    }


    if (! function_exists('str_limit')) {
        /**
         * Limit the number of characters in a string.
         *
         * @param  string  $value
         * @param  int     $limit
         * @param  string  $end
         * @return string
         */
            function str_limit($value, $limit = 100, $end = '...')
            {
                return Str::limit($value, $limit, $end);
            }
    }


    if (! function_exists('str_replace_first')) {
        /**
         * Replace the first occurrence of a given value in the string.
         *
         * @param  string  $search
         * @param  string  $replace
         * @param  string  $subject
         * @return string
         */
        function str_replace_first($search, $replace, $subject)
        {
            return Str::replaceFirst($search, $replace, $subject);
        }
    }



    if (! function_exists('array_pluck')) {
        /**
         * Pluck an array of values from an array.
         *
         * @param  array   $array
         * @param  string|array  $value
         * @param  string|array|null  $key
         * @return array
         */
            function array_pluck($array, $value, $key = null)
            {
                return Arr::pluck($array, $value, $key);
            }
    }

    if (! function_exists('array_collapse')) {
        /**
         * Collapse an array of arrays into a single array.
         *
         * @param  array  $array
         * @return array
         */
            function array_collapse($array)
            {
                return Arr::collapse($array);
            }
    }



    if (! function_exists('title_case')) {
        /**
         * Convert a value to title case.
         *
         * @param  string  $value
         * @return string
         *
         * @deprecated Str::title() should be used directly instead. Will be removed in Laravel 5.9.
         */
            function title_case($value)
            {
                return Str::title($value);
            }
    }


    /**
     * Dispatch emails to queue
     *
     * @param $params
     * @return void
     */
    // function sendEmailWithQueue($params)
    // {
    //     dispatch(new SendEmail($params));
    // }


     /**
     * Convert minutes to days
     * @param (string) $startDate
     * @param (int) $minutes
     */
    if (!function_exists('convertMinutesToDays')) {
        function convertMinutesToDays($startDate, $minutes = null)
        {
            $endDate = Carbon::parse($startDate)->addMinutes($minutes);
            $days = Carbon::now()->diffInDays($endDate, false) ;
            return $days > 0 ? $days : 0;
        }
    }


    /*
    * Checks Authenticaion For REST API Users
    *
    * @params object $request
    * @return boolean(true/false)
    */
    function restAPIAuth(Request $request)
    {
        $response = ['status_code' => 200, 'message' => 'User logged in successfully'];

        // fetching user from database
        $userObj = \Component\AccountComponent\App\User::getRestAPIUserByEmail($request->getUser());
        if (empty($userObj)) {
            $response = ['status_code' => 401, 'message' => 'Unauthorized user.'];
            return $response;
        }
        if (!(Illuminate\Support\Facades\Hash::check($request->getPassword(), $userObj->password))) {
            $response = ['status_code' => 422, 'message' => 'The user credentials are incorrect.'];
        }

        return $response;
    }


    /*
    * Generates unique id as per required length/limit
    *
    * @param string $limit
    * @return string $uniqueCode
    */
    function uniqueCode($limit = "25")
    {
        return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
    }


    /*
    * Generates unique id as per required length/limit
    *
    * @param string $limit
    * @return string $uniqueCode
    */
    if (!function_exists('generateUUID')) {
        function generateUUID()
        {
            $data = openssl_random_pseudo_bytes(16, $secure);
            if (false === $data) { return false; }
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
    }
    

    /**
    * Generates unique id as per required length/limit
    *
    * @param string $method
    * @return string $endPoint
    * @return string $params
    * @return collection
    */
    if (!function_exists('curlRequest')) {

        function curlRequest($method, $endPoint, $params = [],$curlThroughPackage = true)
        {
            $response = null;
            if($curlThroughPackage){ // when guzzle or any package is used
                try{
                    $client = new Client();
                    $response = $client->request($method, $endPoint, $params);
                }catch(\Throwable $e){
                   if($log_reader = getRequestLogData("Exception",$method, $endPoint, $params,$e)){
                       readRequestLog($log_reader);
                   }
                   throw $e;
                }
            }else{
                if("HEAD" == $method ){ // when core curl is used without any package
                    $ch = curl_init();
                    curl_setopt ($ch, CURLOPT_URL, $endPoint);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $params);
                    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_NOBODY, true); // for HEAD call
                    $data = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE );
                    curl_close ($ch);
                    $response = [];
                    $response['statusCode'] = $httpCode;
                    $response['contentType'] = $contentType;
                    $response['content'] = [];
    
                }else if("POST" == $method){
                    \Log::info('post condition');
                    $curl = curl_init();
                    curl_setopt_array($curl, $params);
                    $response = curl_exec($curl);
                    curl_close($curl);
    
                }else if("GET" == $method){
                    $ch = curl_init($endPoint);
                    curl_setopt($ch, CURLOPT_URL, $endPoint);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $response = curl_exec ($ch);
                    curl_close($ch);
    
                }
            }
    
           try{
               if($log_reader = getRequestLogData("POST",$method, $endPoint, $params,$response,$curlThroughPackage)){
                   readRequestLog($log_reader);
               }
           }catch(\Exception $e){
               \Log::error($e->getMessage());
               return $response;
           }catch (\Throwable $t) {
               \Log::error($t->getMessage());
               return $response;
           }
    
                    return $response;
            }
        }


        /**
        * Method to generate logs
        *
        * @param string $position
        * @return string $method
        * @return string $endPoint
        * @return string $params
        * @return string $response
        * @return string $curlThroughPackage
        * @return array
        */

        if (!function_exists('getRequestLogData')) {
            function getRequestLogData($position = "PRE",$method, $endPoint, $params,$response = null,$curlThroughPackage = true){
    
                $userId = \App\Http\Controllers\Controller::$requestLogUserId;
                $userIP = \App\Http\Controllers\Controller::$requestLogUserIP;
                $endpointDetail = getEndpointName($endPoint);
    
                $log_reader = [
                    'endpoint' => $endPoint,
                    'endpoint_name' => !empty($endpointDetail['endpointName']) ? $endpointDetail['endpointName'] : 'N/A',
                    'method' => $method,
                    'params' => $params,
                    'date' => Carbon::now(),
                    'user' => Auth::check() ? Auth::user()->id : ( !empty($userId) ? $userId : null ),
                    'portal_type' => !empty($endPoint) ? (!empty($endpointDetail['portalType']) ? $endpointDetail['portalType'] : "N/A") : "N/A",
    //                'portal_type' => !empty($endPoint) ? RequestLogReader::getExternalPortalType($endPoint) : "N/A",
                    'ip_address' => !empty($userIP) ? $userIP : null ,
                    'agent' => 'CURL',
                ];
    
                if("PRE" == $position){
                    return $log_reader;
                }else if("Exception" == $position){
                    $e = $response;
                    return array_merge($log_reader,[
                        'response' => null,
                        'response_data' => json_encode($e->getMessage()),
                        'headers' => [],
                        'statusCode' => $e->getCode(),
                        'request_type' => RequestLogReader::EXTERNAL_REQUEST_TYPE,
    
                    ]);
                }else if(RequestLogReader::RIVERSEND_CONNECTION == $position){
                    return array_merge($log_reader,[
                        'statusCode' => isset($response["status"]) ? $response['status'] : 0,
                        'headers' => NULL,
                        'agent' => 'Application',
                        'params' => ["table" => $params],
                        'response_data' => isset($response["data"]) ? $response['data'] : [],
                        'request_type' =>  RequestLogReader::EXTERNAL_REQUEST_TYPE,
                        'portal_type' => RequestLogReader::RIVERSEND_CONNECTION,
                        'method' => 'DB Connectivity',
                    ]);
    
                }
                else if('log' == $position){
                    return array_merge($log_reader,[
                        'statusCode' => 200,
                        'headers' => NULL,
                        'agent' => 'Application',
                        'params' => ["table" => $params],
                        'response_data' => $response,
                        'request_type' =>  RequestLogReader::INTERNAL_REQUEST_TYPE,
                        'portal_type' => 'Application',
                        'method' => 'Backend Call'
                    ]);
    
                }
                else if("POST" == $position){
                    if($curlThroughPackage){
                        if(is_object($response) && "GuzzleHttp\Psr7\Response" == get_class($response)){ // if class id this GuzzleHttp\Psr7\Response
                            $log_reader['statusCode'] = $response->getStatusCode();
                            $log_reader['headers'] = $response->getHeaders();
                            $log_reader['response_data'] = json_decode($response->getBody(),true);
                            $log_reader['request_type'] = RequestLogReader::EXTERNAL_REQUEST_TYPE;
                            $response->getBody()->rewind();
                            return $log_reader;
                        }
    
                    }else{
                        $log_reader['headers'] = [];
                        $log_reader['response_data'] = $response;
                        $log_reader['statusCode'] = isset($response['statusCode'])?$response['statusCode'] : ($response ? 200:  0);
                        $log_reader['request_type'] = RequestLogReader::EXTERNAL_REQUEST_TYPE;
                        return $log_reader;
                    }
                }else if("MES" == $position || "WORLD PAY" == $position){
                    $log_reader['headers'] = [];
                    $log_reader['response_data'] = $response;
                    $log_reader['statusCode'] = isset($response['statusCode'])?$response['statusCode'] : ($response ? 200:  0);
                    $log_reader['request_type'] = RequestLogReader::EXTERNAL_REQUEST_TYPE;
                    $log_reader['portal_type'] = $position;
    
                    return $log_reader;
                }
                return false;
    
            }
        } 


          /**
     * @param $salesRepAB
     * @param $salesDivision
     * @return bool|mixed|string
     * get default sales AB
     */
    function getSalesRepAB($salesRepAB, $salesDivision)
    {
        if(strtolower($salesDivision) == 'esg')
        {
            $salesRepAB = ($salesRepAB == 0 || empty($salesRepAB)) ? ENV('DEFAULT_SALES_REP_AB_ESG','301') : $salesRepAB;
        }
        else if(strtolower($salesDivision) == 'psg')
        {
            $salesRepAB = ($salesRepAB == 0 || empty($salesRepAB)) ? ENV('DEFAULT_SALES_REP_AB_PSG','205') : $salesRepAB;
        }
        else if(strtolower($salesDivision) == 'bsg')
        {
            $salesRepAB = ($salesRepAB == 0 || empty($salesRepAB)) ? ENV('DEFAULT_SALES_REP_AB_BSG','201') : $salesRepAB;
        }
        else
        {
            $salesRepAB = ($salesRepAB == 0 || empty($salesRepAB)) ? ENV('DEFAULT_SALES_REP_AB_CCM','201') : $salesRepAB;
        }

        return $salesRepAB;
    }


    /*
    * Generates encrypted value
    *
    * @param string $string
    * @return string $text
    */
    if (!function_exists('encryptString')) {

        function encryptString($string)
        {
            try { 

                $cipher_method = 'AES-256-CBC';
                $enc_key = config('main.encrypt_decrypt_key_1'); 
                $enc_iv = config('main.encrypt_decrypt_key_2'); 
                
                $encrypted = openssl_encrypt($string, $cipher_method, $enc_key, 0, $enc_iv); 

            } catch (DecryptException $e) {
                 //In case of any exception return the plain text
                 $encrypted = $string;
            }

            return $encrypted;
        }
    }

    /*
    * Generates decrypted value
    *
    * @param string $string
    * @return string $text
    */
    if (!function_exists('decryptString')) {
        function decryptString($encryptedValue)
        {
            try {        

                $enc_key = config('main.encrypt_decrypt_key_1'); 
                $enc_iv = config('main.encrypt_decrypt_key_2'); 
                $cipher_method = 'AES-256-CBC';

                $decrypted = openssl_decrypt($encryptedValue, $cipher_method, $enc_key, 0, $enc_iv);                  

            } catch (DecryptException $e) {
                 //In case of any exception return the plain text
                 $decrypted = $encryptedValue;
            }

            return $decrypted;
        }
    }


    /*
    * filter incoming zipcode
    *
    * @param string $zipcode
    * @return string $zipcode
    */
    if (!function_exists('filterZipcode')) {
        function filterZipcode($zipcode)
        {
            try {
                if(!empty($zipcode)) {
                    $zipcode = preg_replace('/[^0-9]/i', '', $zipcode); // remove all characters except numbers
                    //$zipcode = ltrim($zipcode, "0"); // remove leading zeros
                    $zipcode = substr($zipcode, 0, 5); // accept first five character
                }
            } catch (\Exception $e) {
                \Log::info("filter zipcode");
                \Log::info($e->getMessage());
            }
            return $zipcode;
        }
    }


     /*
    * provide array to get valid phone number
    *
    * @param array $phones
    * @return string $valid_phone
    */

    if (!function_exists('getValidPhone')) {
        function getValidPhone($phones)
        {
            $valid_phone = "";
            if (is_array($phones)) {
                foreach ($phones as $phone) {
                    $c_phone = preg_replace('/(\.|\-|\+)+/', '', $phone['prefix'] . $phone['number']);
                    $phone_len = strlen((string)$c_phone);
                    if ($phone_len != 10)
                        continue;
                    $format_number = "+1-" . substr($c_phone, 0, 3) . '-' . substr($c_phone, 3, 3) . '-' . substr($c_phone, 6, 4);
                    if (preg_match("/^\+1\-[1-9]{1}[0-9]{2}\-[0-9]{3}\-[0-9]{4}$/", $format_number)) {
                        $valid_phone = $format_number;
                        break;
                    }
                }
            }
            return $valid_phone;
        }
    }

    /*
    * Validation is US Zipcode
    *
    * @param string $zipcode
    * @return boolean $isValid
    * */
    if (!function_exists('isValidUSZipcode')) {
        function isValidUSZipcode($zipcode)
        {
            $isValid = false;
            try {
                if (\Component\AccountComponent\App\Zipcode::where('postal_code', $zipcode)->count() > 0)
                    $isValid = true;
            } catch (\Exception $e) {
                \Log::info("Exception *isValidUSZipcode*: " . $e->getMessage());
            }
            return $isValid;
        }
    }


    /**
     * Get get Plugins Directory
     * @param $parentId
     * @return String
     */

    function getPluginsDirectory($parentId) {
        $plugins_dir = config('main.plugins_dir');

        $dir_name = '';
        if (isset($plugins_dir[$parentId])) {
            $dir_name = $plugins_dir[$parentId];
        }

        return $dir_name;
    }

    /**
     * Sync Provider with Queue
     *
     * @param $params
     * @return void
     */
    function syncProviderWithQueue($params)
    {
        dispatch(new SyncProviders($params));
    }

    /**
     * Sync Addons with Queue
     *
     * @param $params
     * @return void
     */
    function syncAddonWithQueue($params)
    {
        dispatch(new SyncAddons($params));
    }

    /**
     * Sync Categories with Queue
     *
     * @param $params
     * @return void
    */
    function syncCategoriesWithQueue($params)
    {
        dispatch(new SyncCategories($params));
    }

     /**
     * Sync Service with Queue
     *
     * @param $params
     * @return void
     */
    function syncServiceWithQueue($params)
    {
        dispatch(new SyncService($params));
    }


    /**
     * Function that returns time period of subscription end date
     *
     * @param $subscription_start_date
     * @param $timePeriodForService
     * @return string
     */
    function updateSubscriptionEndDate($subscription_start_date, $timePeriodForService) {
        $subscriptionEndDate = '';
        if ($timePeriodForService == "1 Month") {
            $timePeriod = "1 month";
            $subscriptionEndDate = date('Y-m-d',strtotime($subscription_start_date . " + ". $timePeriod));
        } elseif ($timePeriodForService == "3 months" || $timePeriodForService == "3 Months"){    // Added as per discussion with Faizan CCP-5379
            $timePeriod = "3 months";
            $subscriptionEndDate = date('Y-m-d',strtotime($subscription_start_date . " + ". $timePeriod));
        } elseif ($timePeriodForService == "1 Year" || ($timePeriodForService == '' || $timePeriodForService == null)) {
            $timePeriod = "365 day";
            $subscriptionEndDate = date('Y-m-d',strtotime($subscription_start_date . " + ". $timePeriod));
        } elseif ($timePeriodForService == "2 Years") {
            $timePeriod = "730 day";
            $subscriptionEndDate = date('Y-m-d',strtotime($subscription_start_date . " + ". $timePeriod));
        } elseif ($timePeriodForService == "3 Years") {
            $timePeriod = "1095 day";
            $subscriptionEndDate = date('Y-m-d',strtotime($subscription_start_date . " + ". $timePeriod));
        }

        return $subscriptionEndDate;
    } 


   /**
     * Get error detail
     * @param $data
     * @return mixed
     */
    if (!function_exists('getErrorDetail')) {
        function getErrorDetail($lastRecord, $providerName) {
            $errorDetail = [];
            switch ($providerName) {
                case "microsoft":
                    if (Arr::has($lastRecord, ['content.code'])) {
                        $errorDetail['error_code'] = Arr::get($lastRecord, 'content.code', '');
                        $errorDetail['error_description'] = Arr::get($lastRecord, 'content.description', '');
                    } else {
                        $errorDetail['error_code'] = Arr::get($lastRecord, 'statusCode', '');
                        $errorDetail['error_description'] = Arr::get($lastRecord, 'content', '');
                    }
                    break;
                case "adobe":
                    $errorDetail['error_code'] =  Arr::get($lastRecord,'content.code','');
                    $errorDetail['error_description'] =  Arr::get($lastRecord,'content.message','');
                    break;
                case "aws":
                    if (Arr::has($lastRecord, ['content.Message'])) {
                        $errorDetail['error_code'] =  Arr::get($lastRecord,'statusCode','');
                        $errorDetail['error_description'] =  Arr::get($lastRecord,'content.Message','');
                    } else {
                        $errorDetail['error_code'] =  Arr::get($lastRecord,'content.Error.Code','');
                        $errorDetail['error_description'] =  Arr::get($lastRecord,'content.Error.Message','');
                    }
                    break;
                case "wasabi":
                    $errorDetail['error_code'] =  Arr::get($lastRecord,'statusCode','');
                    $errorDetail['error_description'] =  Arr::get($lastRecord,'content.Msg','');
                    break;
                case "acronis":
                    $errorDetail['error_code'] =  Arr::get($lastRecord,'content.error.code','');
                    $errorDetail['error_description'] =  Arr::get($lastRecord,'content.error.message','');
                    break;
                case "dropbox":
                    $errorDetail['error_code'] =  Arr::get($lastRecord,'content.error_code','');
                    $errorDetail['error_description'] =  Arr::get($lastRecord,'content.errors','')[0];
                    break;
                default:
                    $errorDetail['error_code'] =  '';
                    $errorDetail['error_description'] =  '';
                }

                return $errorDetail;
       }
   }
        