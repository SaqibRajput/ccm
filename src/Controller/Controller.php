<?php

    namespace CCM\Leads\Controller;

    use Illuminate\Http\Request;
    use Laravel\Lumen\Routing\Controller as LumenController;

    class Controller extends LumenController
    {
        public function __construct(Request $request)
        {

        }

        public function apiResponse($data)
        {
            return $data;
        }
    }
