<?php

    namespace CCM\Leads\Controller;

    use Validator;
    use Illuminate\Http\Request;
    use Laravel\Lumen\Routing\Controller as LumenController;

    use CCM\Leads\Jobs\SendEmail;
    use CCM\Leads\Jobs\LoginLog;
    use CCM\Leads\Jobs\AuditLog;

    class Controller extends LumenController
    {
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
            ], []);

            if ($validator->fails())
            {
                return createResponseData(422, false, $validator->errors());
            }
            try
            {
                // need to add columns here
                $model = "App\\Models\\".$request->model;
                $where = $request->where;
                $tableData = $model::where($where)->get();

                return createResponseData(200, true, '', $tableData);
            }
            catch(\Exception $ex)
            {
                return createResponseData(422, false, $ex->getMessage());
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
