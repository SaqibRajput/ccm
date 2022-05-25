<?php

    namespace CCM\Leads\Controllers;

    use Validator;
    use Illuminate\Http\Request;

    use Laravel\Lumen\Routing\Controller as LumenController;

    use CCM\Leads\Jobs\SendEmail;
    use CCM\Leads\Jobs\LoginLog;
    use CCM\Leads\Jobs\AuditLog;
    use CCM\Leads\Jobs\sendNotification;

    use CCM\Leads\Traits\CustomValidation;

    class Controller extends LumenController
    {
        use CustomValidation;

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

        // send log request with queue
        public function loginLog($request, $success = true, $message = '') {

            $user = $request->user ?? null;

            $request->merge([
                'success' => $success,
                'message' => is_object($message) ? json_encode($message) : $message,
                'user' => $user
            ]);

            dispatch(new LoginLog($request));
        }

        // send audit log request with queue
        public function auditLog($request, $object, $data = [], $type = 'select', $updatedData = []) {

            $request->merge([
                'params' => [
                    'type' => $type,
                    'object' => $object,
                    'data' => $data,
                    'updatedData' => $updatedData // in case of update.
                ]
            ]);

            dispatch(new AuditLog($request));
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
    }
