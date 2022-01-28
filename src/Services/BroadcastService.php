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

    class BroadcastService extends Service
    {
        public $baseUri;
        public $secret;

        public function __construct()
        {
            parent::__construct();
            $this->baseUri = config('services.broadcast.base_uri');
            $this->secret  = config('services.broadcast.secret');
        }

        /* Authenticate Controller */
        public function sendEmail($param)
        {
            return $this->callOtherService('POST', 'send-email', $param);
        }

    }
