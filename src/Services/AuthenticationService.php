<?php

    namespace CCM\Leads\Services;

    use Illuminate\Http\Request;

    class AuthenticationService extends Service
    {
        public $baseUri;
        public $secret;

        public function __construct()
        {
            parent::__construct();
            $this->baseUri = config('services.authentication.base_uri');
            $this->secret = config('services.authentication.secret');
        }

        // for user authentication.
        public function apiAuthentication()
        {
            return $this->callOtherService('POST', "/auth/api-authentication", [], ['Authorization' => app('request')->headers->get('Authorization')]);
        }

        public function login(Request $request)
        {
            return $this->callOtherService('POST', '/auth/login', $request);
        }

        /* User Controller */
        public function users()
        {
            return $this->callOtherService('GET', '/users');
        }

        public function passed()
        {
            return $this->callOtherService('GET', '/users/passed');
        }

        public function failed()
        {
            return $this->callOtherService('GET', '/users/failed');
        }
    }
