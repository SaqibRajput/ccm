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
        // create new api for internal services call
        public function getCompany($companyId)
        {
            $param = ['company_id' => $companyId];
            return $this->callOtherService('GET', 'account/company/'.$companyId, $param);
        }

        public function deleteIncompleteSignup($companyId)
        {
            $param = ['company_id' => $companyId];
            return $this->callOtherService('POST', 'account/company/delete-incomplete-signup', $param);
        }

        // need to fix this function
        public function getCompaniesListByDomain($email)
        {
            $param = ['emails' => $email];
            return $this->callOtherService('POST', 'account/company/list-by-domain', $param);
        }
        // create new api for internal services call

    }
