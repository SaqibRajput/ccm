<?php

    namespace CCM\Leads\Services;

    use Illuminate\Http\Request;

    class MonolithService extends Service
    {
        public $baseUri;
        public $secret;

        public function __construct()
        {
            parent::__construct();
            $this->baseUri = config('services.monolith.base_uri');
            $this->secret = config('services.monolith.secret');
        }

        public function callAPI($method, $url, $data, $headers)
        {
            return $this->callOtherService($method, $url, $data, $headers);
        }

    }
