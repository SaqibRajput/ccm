<?php

    // need to create sub services via controller name
    // to avoid file size increase
    // to face code refactor

    namespace CCM\Leads\Services;

    use Illuminate\Http\Request;

    class CatalogsService extends Service
    {
        public $baseUri;
        public $secret;

        public function __construct()
        {
            parent::__construct();
            $this->baseUri = config('services.catalogs.base_uri');
            $this->secret = config('services.catalogs.secret');
        }

        public function isIndustryTypesVerified($industryTypesId)
        {
            $param = ['industryTypesId' => $industryTypesId];
            return $this->callOtherService('POST', "/industry-types-verified", $param, ['Authorization' => app('request')->headers->get('Authorization')]);
        }

        public function syncIndustryTypes()
        {
            $param = ['industryTypesId' => $industryTypesId];
            return $this->callOtherService('POST', "/sync-industry-types", $param, ['Authorization' => app('request')->headers->get('Authorization')]);
        }
    }
