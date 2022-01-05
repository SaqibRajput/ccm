<?php

    namespace CCM\Leads\Services;

    use Illuminate\Http\Request;

    class AuthService extends Service
    {
        public $baseUri;
        public $secret;

        public function __construct()
        {
            $this->baseUri = config('services.auth.base_uri');
            $this->secret = config('services.auth.secret');
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
