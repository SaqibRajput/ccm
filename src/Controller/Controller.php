<?php

    namespace CCM\Leads\Controller;

    use Illuminate\Http\Request;
    use Laravel\Lumen\Routing\Controller as LumenController;

    use CCM\Leads\Jobs\SendEmail;
    use CCM\Leads\Jobs\LoginLog;
    use CCM\Leads\Jobs\AuditLog;
    
    class Controller extends LumenController
    {
        public function __construct(Request $request)
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
    }
