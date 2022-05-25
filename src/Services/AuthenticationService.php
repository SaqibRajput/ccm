<?php

    // need to create sub services via controller name
    // to avoid file size increase
    // to face code refactor

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

        // login calls
        public function login(Request $request)
        {
            return $this->callOtherService('POST', '/auth/login', $request);
        }
        // login calls

        /* User Controller */
        public function emailExist()
        {
            // create function in authentication
            return $this->callOtherService('GET', '/email-exist');
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
