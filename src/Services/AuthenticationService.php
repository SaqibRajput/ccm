<?php

    namespace CCM\Leads\Services;

    use Illuminate\Http\Request;

    class AuthenticationService extends Service
    {
        public $baseUri;
        public $secret;

        public function __construct()
        {
            $this->baseUri = config('services.authentication.base_uri');
            $this->secret = config('services.authentication.secret');
        }

        /* Authenticate Controller */
        public function login(Request $request)
        {
            return $this->callExternalService('POST', '/auth/login', $request);
        }

        /* User Controller */
        public function users()
        {
            return $this->callExternalService('GET', '/users');
        }

        public function passed()
        {
            return $this->callExternalService('GET', '/users/passed');
        }

        public function failed()
        {
            return $this->callExternalService('GET', '/users/failed');
        }
    }
