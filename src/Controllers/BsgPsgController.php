<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 19/01/2022
     * Time: 7:54 PM
     */

    namespace CCM\Leads\Controllers;

    use App\Http\Controllers\Controller as BaseController;


    class BsgPsgController extends BaseController
    {
        // U-1783618@U-1783618.com
        private $timeOut;

        function __construct() {
            $this->timeOut = config('bsgpsg.time_out');
        }

        public function accountMatching($email)
        {
            $response['success'] = false;

            if(config('bsgpsg.skip_call') === true)
            {
                lumenLog("skipped BSG PSG call.");
                return $response;
            }

            try
            {
                $authResponse = $this->authentication();

                if ($authResponse['success'])
                {
                    $authToken      = $authResponse['data']['accessToken']['token'];
                    $searchResponse = $this->search( ['email' => $email, 'auth_token' => $authToken] );

                    if ($searchResponse['success'])
                    {
                        $groupResponse = $this->getGroups( ['userId' => $searchResponse['data']['users'][0]['userId'], 'auth_token' => $authToken ] );

                        $groupDetailResponse = [];
                        if($groupResponse['success'])
                        {
                            $groupDetailResponse = $this->getGroupDetail($groupResponse, $authToken);
                        }

                        $result['data']['platform']       = $searchResponse['data']['users'][0]['website'];
                        $result['data']['group']          = $groupResponse['data'];
                        $result['data']['groupDetails']   = $groupDetailResponse;
                        $result['data']                   = json_encode($searchResponse['data']);

                        $response['data']    = $this->dataMapping($result['data'] );
                        $response['success'] = true;
                    }
                }
            }
            catch (\Exception $ex)
            {
                lumenLog('Exception : '.$ex->getMessage());
                $data['success'] = false;
                $data['statusCode'] = (method_exists($ex, 'getCode') ? $ex->getCode() : 500);
            }

            lumenLog('bsg psg account matching');
            lumenLog($response);

            return $response;
        }

        /**
         * BSG & PSG platform authentication call
         *
         * @params (string) $email
         * @return (GuzzleClient) $response
         */
        public function authentication()
        {
            $response = [ 'success' => false ];

            try
            {
                $url            = config('bsgpsg.auth_url').config('bsgpsg.apis.authentication.endpoint');
                $params         = config('bsgpsg.apis.authentication.params');
                $params['json'] = config('bsgpsg.apis.authentication.json');
                $method         = config('bsgpsg.apis.authentication.method');

                $response = curlRequest($method, $url, $params, $this->timeOut);

                if ($response != null && $response->getStatusCode() == 200)
                {
                    $response = [
                        'success' => true,
                        'data' => json_decode($response->getBody()->getContents(), true),
                    ];
                }

            }
            catch (\Exception $ex)
            {
                lumenLog('Exception : '.$ex->getMessage());
                $data['success'] = false;
                $data['statusCode'] = (method_exists($ex, 'getCode') ? $ex->getCode() : 500);
            }

            return $response;
        }


        /**
         * BSG & PSG Search integration
         *
         * @params (array) $param
         * @return (GuzzleClient) $response
         */
        private function search($param)
        {
            $data = [ 'success' => false, 'data' => [] ];

            try
            {
                $method             = config('bsgpsg.apis.search.method');
                $url                = config('bsgpsg.base_url').config('bsgpsg.apis.search.endpoint');
                $request            = config('bsgpsg.apis.search.params');
                $request['query']   = ['SearchEmailAddress' => $param['email']];

                $request['headers'] = [
                    'Authorization' => 'Bearer ' . $param['auth_token'],
                ];

                $response = curlRequest($method, $url, $request, $this->timeOut);

                if ($response != null && $response->getStatusCode() == 200)
                {
                    $data['data'] = json_decode($response->getBody()->getContents(), true);

                    if(count($data['data']['users']) > 0)
                    {
                        $data['success'] = true;
                    }
                }

            }
            catch (\Exception $ex)
            {
                lumenLog('Exception : '.$ex->getMessage());
                $data['success'] = false;
                $data['statusCode'] = (method_exists($ex, 'getCode') ? $ex->getCode() : 500);
            }

            return $data;
        }


        /**
         * BSG & PSG get groups integration
         *
         * @params (array) $params
         * @return (GuzzleClient) $response
         */
        private function getGroups( $params )
        {
            $data = [ 'success' => false, 'data' => [] ];
            $request = [];

            try
            {
                $staticUrl  = config('bsgpsg.base_url').config('bsgpsg.apis.groups.endpoint');
                $url        = str_replace('{user_id}', $params['userId'], $staticUrl);
                $method     = config('bsgpsg.apis.groups.method');
                $request    = config('bsgpsg.apis.groups.params');

                $request['headers'] = [
                    'Authorization' => 'Bearer ' . $params['auth_token'],
                ];

                $response = curlRequest($method, $url, $request, $this->timeOut);

                if ($response != null && $response->getStatusCode() == 200)
                {
                    $data['data'] = json_decode($response->getBody()->getContents(), true);

                    if(isset($data['data']['groups']) && count($data['data']['groups']) > 0)
                    {
                        $data['success'] = true;
                    }
                }
            }
            catch (\Exception $ex)
            {
                lumenLog('Exception : '.$ex->getMessage());
                $data['success'] = false;
                $data['statusCode'] = (method_exists($ex, 'getCode') ? $ex->getCode() : 500);
            }

            return $data;
        }

        /**
         * exec bsg psg groups call
         * @param $groupResponse
         * @param $authToken
         * @return \Illuminate\Support\Collection
         */
        public function getGroupDetail($groupResponse, $authToken)
        {
            $groupDetails = [];

            if(isset($groupResponse['data']['groups']['groups']) && count($groupResponse['data']['groups']['groups']) > 0)
            {
                $groupResponseCollect = collect($groupResponse['data']['groups']['groups']);

                $groupDetails = $groupResponseCollect->map(function($group) use ($authToken) {

                    $group['auth_token'] = $authToken;
                    $groupDetail = $this->groupDetailCall($group);

                    if($groupDetail['success']){
                        return $groupDetail['data'];
                    }

                });
            }

            return $groupDetails;
        }

        /**
         * BSG & PSG get group detail integration
         *
         * @params (array) $params
         * @return (GuzzleClient) $response
         */
        public function groupDetailCall( $params )
        {
            $data = [ 'success' => false, 'data' => [] ];
            $request = [];

            try
            {
                $url        = config('bsgpsg.base_url').config('bsgpsg.apis.groupDetail.endpoint').$params['groupId'];
                $method     = config('bsgpsg.apis.groups.method');
                $request    = config('bsgpsg.apis.groups.params');

                $request['headers'] = [
                    'Authorization' => 'Bearer ' . $params['auth_token'],
                ];

                $response = curlRequest($method, $url, $request, $this->timeOut);

                if ($response != null && $response->getStatusCode() == 200)
                {
                    $data['success'] = true;
                    $data['data'] = json_decode($response->getBody()->getContents(), true);
                }

            }
            catch (\Exception $ex)
            {
                lumenLog('Exception : '.$ex->getMessage());
                $data['success'] = false;
                $data['statusCode'] = (method_exists($ex, 'getCode') ? $ex->getCode() : 500);
            }

            return $data;
        }


        /**
         * Manipulate BST response by idealizing ESG response
         * @param JSON $accountMatching
         * @return JSON $bsgResponse
         *
         */
        private function dataMapping($accountMatching)
        {
            $apiResponse    = json_decode($accountMatching, true);
            $platform       = empty($apiResponse['platform']) ? $apiResponse['users'][0]['website'] : $apiResponse['platform'];

            $response[]  = [
                'active' => $apiResponse['users'][0]['isActive'],
                'userId' => $apiResponse['users'][0]['userId'],
                'email' => $apiResponse['users'][0]['emailAddress'],
                'firstName' => $apiResponse['users'][0]['firstName'],
                'lastName' => $apiResponse['users'][0]['lastName'],
                'platform' => (strtoupper($platform) == 'PCCB2B') ? 'BSG' : 'PSG',
                'userStatus' => ($apiResponse['users'][0]['isActive'] == true) ? 'Active' : 'Inactive' ,
                'userType' => $apiResponse['users'][0]['isAccountManager'] ?? '',
                'userTypeId' => '',
                'homePageUrl' => '',
                'approverType' => '',
                'maxApprovalAmt' => '',
                'minApproverAmt' => '',
                'createdDateTime' => '',
                'modifiedDateTime' => '',
                'division' => '',
                'modifiedBy' => '',
                'createdBy' => '',
                'zip' => '',
                'state' => '',
                'address1' => '',
                'address2' => '',
                'defaultShipno' => '',
                'phoneExt' => '',
                'mobilePhone' => '',
                'city' => '',
                'fax' => '',
                'authenticatedLinkHash' => '',
                'accountCode' => '',
                'accountName' => '',
                'accountId' => '',
                'preferredInterface' => '',
                'accountActive' => '',
                'userClass' => '',
                'account' => [],
                'organizationName' => $apiResponse['group']['organizationName'] ?? '',
                'groups' => $apiResponse['groupDetails']?? [],
                'originalResponseBody' => $accountMatching,
            ];

            return json_encode($response);
        }

    }
