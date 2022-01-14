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
            parent::__construct();
            $this->baseUri = config('services.companies.base_uri');
            $this->secret  = config('services.companies.secret');
        }

        /* Authenticate Controller */
        public function getCompany($company_id)
        {
            $param = ['company_id' => $company_id];
            return $this->callOtherService('POST', '/', $param);
        }

    }
