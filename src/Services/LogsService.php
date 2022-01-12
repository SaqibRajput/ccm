<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 07/01/2022
     * Time: 16:29
     */

    namespace CCM\Leads\Services;

    use Illuminate\Http\Request;

    class LogsService extends Service
    {
        public $baseUri;
        public $secret;

        public function __construct()
        {
            $this->baseUri = config('services.logs.base_uri');
            $this->secret  = config('services.logs.secret');
        }

        /* Authenticate Controller */
        public function login(Request $request)
        {
            return $this->callExternalService('POST', '/login', $request->toArray());
        }

    }
