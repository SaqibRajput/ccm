<?php

    namespace CCM\Leads\Controllers;

    use Validator;
    use Illuminate\Http\Request;
    use Laravel\Lumen\Routing\Controller as LumenController;
    use CCM\Leads\Jobs\SendEmail;
    use CCM\Leads\Jobs\EventLog;
    use CCM\Leads\Jobs\LoginLog;
    use CCM\Leads\Jobs\SendNotification;
    use Carbon\Carbon;
    use CCM\Leads\Traits\CustomValidation;
    use CCM\Leads\Controllers\EsgController;

    class Controller extends LumenController
    {
        use CustomValidation;

        public static $requestLogUserIP;
        

        public const RESPONSE_SUCCESS = true;
        public const RESPONSE_FAILED = false;

        const STATUS_CODE_SUCCESS = 200;
        const STATUS_CODE_AUTHENTICATION_FAILED = 400;
        const STATUS_CODE_SERVICE_FAILED = 421;
        const STATUS_CODE_FAILED = 422;
        const STATUS_CODE_VALIDATION_FAILED = 423;
        const STATUS_CODE_DB_FAILED = 424;
        const STATUS_CODE_INVALID_DATA_FAILED = 425;
        const STATUS_CODE_CURL_FAILED = 408;

        const CONST_BSG = 'bsg';
        const CONST_PSG = 'psg';
        const CONST_ESG = 'esg';

        public function __construct()
        {

        }

        public function apiResponse($data)
        {
            return $data;
        }

        // send email with queue
        public function sendEmail($params) {
           
           dispatch(new SendEmail($params));
        }

        public function sendNotification($params) {
            dispatch(new SendNotification($params));
        }

        // public function loginLog($params) {
        //     dispatch(new LoginLog($params));
        // }

        // send log request with queue
        public function loginLog($request, $success = true, $message = '') {

            // $user = $request->user ?? null;

            // $request->merge([
            //     'success' => $success,
            //     'message' => is_object($message) ? json_encode($message) : $message,
            //     'user' => $user
            // ]);

            dispatch(new LoginLog($request));
        }

        // send audit log request with queue
        // public function auditLog($request, $object, $data = [], $type = 'select', $updatedData = []) {

        //     $request->merge([
        //         'params' => [
        //             'type' => $type,
        //             'object' => $object,
        //             'data' => $data,
        //             'updatedData' => $updatedData // in case of update.
        //         ]
        //     ]);

        //     dispatch(new AuditLog($request));
        // }

        /**
         * Log event against action
         * @param array $request_data
         * @param string $actionable
         * @param string $actionable_relation
         * @return bool|mixed
         */
        function logEvent($request_data = [], $actionable = '', $actionable_relation = '')
        {
            
            
            if (!empty($request_data)) {
                if (is_object($request_data) && $request_data instanceof Request) {
                    $data = clone $request_data;
                }else{
                    $data = new Request($request_data);
                }

                if (empty($data->user())) {
                    return true;
                }
                
                
                // This will modify $data internally
                resolveUserData($data);
                
                
                $data->merge([
                    'date_time' => Carbon::now()->format('Y-m-d H:i:s'),
                    'operating_system' => getOS($data),
                    'browser' => getBrowser($data),
                    'ip' => !empty($data->ip()) ? $data->ip() : $data->get('ip'),
                    'request' => $data->all(),
                    'portal_type' => $data->portal_type,
                ]);
               
                

                if(!empty(optional($data)->header('Manager-Admin-Email')))
                    $data->merge(['manager_admin_email' => optional($data)->header('Manager-Admin-Email')]);
                if(!empty(optional($data)->header('Manager-Admin-Name')))
                    $data->merge(['manager_admin_name' => optional($data)->header('Manager-Admin-Name')]);
                if(!empty(optional($data)->header('Assume-Role')))
                    $data->merge(['assume_role' => optional($data)->header('Assume-Role')]);

                  
                if (!empty($actionable) && is_object($actionable)) {
                    $data->merge([
                        // CCP-4668
                        'actionable' => (getAbsClassName($actionable) == "ServiceProviderPOC") ? "Service Provider POC" : getAbsClassName($actionable),
                        'actionable_name' => !empty($data->get('actionable_name')) ? $data->get('actionable_name') : 'N/A',
                        'actionable_id' => !empty($data->get('actionable_id')) ? $data->get('actionable_id') : '',
                    ]);
                }
                

                

                if($data->get('customClass') == 'EndUserLicenseAgreement'){
                    $data->merge([
                        // CCP-6326 forcely update actionable name
                        'actionable' => 'EndUserLicenseAgreement'
                    ]);
                }

                if (!empty($actionable_relation) && is_object($actionable_relation)) {
                    $data->merge([
                        'actionable_relation' => getAbsClassName($actionable_relation),
                        'actionable_relation_name' => !empty($data->get('actionable_relation_name')) ? $data->get('actionable_relation_name') : 'N/A',
                        'actionable_relation_id' => !empty($data->get('actionable_relation_id')) ? $data->get('actionable_relation_id') : '',
                    ]);
                }
                

                $log_data = $data->only([
                    'actionable',
                    'actionable_id',
                    'actionable_name',
                    'actionable_relation',
                    'actionable_relation_id',
                    'actionable_relation_name',
                    'user_id',
                    'user_name',
                    'from',
                    'request',
                    'to',
                    'action',
                    'portal_type',//
                    'message',
                    'browser',//
                    'ip',//
                    'operating_system',//
                    'date_time',//
                    'company_id',
                    'manager_admin_email',
                    'manager_admin_name',
                    'assume_role',
                    'organization',
                    'account'
                ]);

               

                dispatch(new EventLog($log_data));
               
            } else {
                throw new \Exception("Exception raised while adding audit log. No request data provided");
            }
        }

       
        public function getServiceData(Request $request)
        {
            lumenLog('getServiceData');
            lumenLog($request->all());

            $validator = validator::make($request->all(), [
                'model' => 'required',
                'where' => 'required|array',
                'with' => 'array',
                'select' => 'array',
            ], []);

            if ($validator->fails())
            {
                return createResponseData(422, false, $validator->errors());
            }

            try
            {
                // need to add columns here
                $model = "App\\Models\\".$request->model;

                // just added to use default where
                // in case it appear exception changed logic
                $query = $model::whereNotNull('created_at');

                if(!empty($request->select))
                {
                    $query->select($request->select);
                }

                foreach ($request->where as $key => $val)
                {
                    $column = $val[0];
                    $value = $val[1];
                    $type = (!isset($val[2]) || $val[2] == '=' || $val[2] == '==') ? '=' : $val[2];

                    lumenLog('$column : '.print_r($column,1).' - $value : '.print_r($value, 1).' - $type : '.print_r($type,1));

                    if($type == '=' || $type == '!=')
                    {
                        $query->where($column, $type, $value);
                    }
                    else if($type == 'in')
                    {
                        $value = (!is_array($value)) ? [$value] : $value;
                        $query->whereIn($column, $value);
                    }
                }

                if(!empty($request->with))
                {
                    $query->with($request->with);
                }

                $tableData = $query->get();

                return createResponseData(self::STATUS_CODE_SUCCESS, self::RESPONSE_SUCCESS, '', $tableData);
            }
            catch(\Exception $ex)
            {
                lumenLog($ex->getTrace());
                return createResponseData(self::STATUS_CODE_INVALID_DATA_FAILED, self::RESPONSE_FAILED, $ex->getMessage());
            }
            catch(\Throwable $t)
            {
                lumenLog($t->getTrace());
                return createResponseData(self::STATUS_CODE_INVALID_DATA_FAILED, self::RESPONSE_FAILED, $t->getMessage());
            }
        }

        public function createServiceData(Request $request)
        {
            lumenLog('createServiceData');
            lumenLog($request->all());

            $validator = validator::make($request->all(), [
                'model' => 'required',
                'data' => 'required|array',
                'where' => 'required|array',
            ], []);

            if ($validator->fails())
            {
                return createResponseData(422, false, $validator->errors());
            }
            try
            {
                $model = "App\\Models\\".$request->model;
                $data = $request->data;
                $where = $request->where;

                $tableData = $model::where($where)->update($data);

                return createResponseData(200, true, '', $tableData);
            }
            catch(\Exception $ex)
            {
                return createResponseData(422, false, $ex->getMessage());
            }
        }

        public function updateServiceData(Request $request)
        {
            lumenLog('updateServiceData');
            lumenLog($request->all());

            $validator = validator::make($request->all(), [
                'model' => 'required',
                'data' => 'required|array',
                'where' => 'required|array',
            ], []);

            if ($validator->fails())
            {
                return createResponseData(422, false, $validator->errors());
            }
            try
            {
                $model = "App\\Models\\".$request->model;
                $data = $request->data;
                $where = $request->where;

                $tableData = $model::where($where)->update($data);

                return createResponseData(200, true, '', $tableData);
            }
            catch(\Exception $ex)
            {
                return createResponseData(422, false, $ex->getMessage());
            }
        }

        public function deleteServiceData(Request $request)
        {
            lumenLog('deleteServiceData');
            lumenLog($request->all());

            $validator = validator::make($request->all(), [
                'model' => 'required',
                'where' => 'required|array',
            ], []);

            if ($validator->fails())
            {
                return createResponseData(422, false, $validator->errors());
            }
            try
            {
                $model = "App\\Models\\".$request->model;
                $where = $request->where;

                $tableData = $model::where($where)->delete();

                return createResponseData(200, true, '', $tableData);
            }
            catch(\Exception $ex)
            {
                return createResponseData(422, false, $ex->getMessage());
            }
        }

        // NEEDS TO BE HANDLED ALL THE PRICE CALL WITH SINGLE SKU
        // REPLACE ALL THE VARIABLE NAME IN PROJECT WHERE CALL THIS FUNCTION
        // ======================================================

        /**
         * Function to send Price call
         * @param  $request
         * @param  $skuId
         * @param  $quantities
         * it called from any component directly.
         */ 
        function getPriceCall($request, $skuId, $quantities = [])
        { 
            $request->merge(['skus' => $skuId]);
            $request->merge(['quantities' => $quantities]);

            return $this->getPriceAPICall($request); 
        } 
        
        /**
         * Function to send Price call
         * @param  $request
         * @param  $skuId
         * @param  $quantities
         * it called through url /v1/get-price-call
         */ 
        function getPriceAPICall(Request $request)
        {    
            $skuId = $request->get('skus');
            $request->merge(['skus' => (is_array($skuId) ? $skuId[0] : $skuId)]);
            $data = [];
            $quantities = $request->get('quantities');
            $request->merge(['quantities' => (is_array($quantities) ? $quantities[0] : $quantities)]);


            $salesDivision = strtolower($request->user()->company->sales_division);
            $accountAB = $request->user()->company->address_book_no;

            // assigning $requestLogUserIP from order to variable which is defined in parent controller
            self::$requestLogUserIP = $request->ip();

            if($salesDivision == self::CONST_ESG)
            {
                // needs to be check why we add this value in request.
                $request->merge(['accountCode' => $request->user()->company->esg_account_code]); 

                $leadEsgObj = new EsgController(); 
                $data = $leadEsgObj->priceCall($request);  
                
            } 
            else if(in_array($salesDivision, [self::CONST_BSG, self::CONST_PSG])){
                // $data = $this->getBsgPsgPrice($request, $accountAB);
            } else {
                // $data = $this->getE1Price($request);
            }

            return $data; 
        }

    }
