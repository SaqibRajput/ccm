<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 12/01/2022
     * Time: 15:16
     */

    namespace CCM\Leads\Services;

    use Illuminate\Http\Request;

    class CompaniesService extends Service
    {
        public $baseUri;
        public $secret;

        public function __construct()
        {
            $this->baseUri = config('companies.logs.base_uri');
            $this->secret  = config('companies.logs.secret');
        }

        /* Authenticate Controller */
        public function login(Request $request)
        {
            return $this->callExternalService('POST', '/', $request->toArray());
        }

    }
