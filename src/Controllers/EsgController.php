<?php
    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 20/01/2022
     * Time: 6:41 PM
     */

    namespace CCM\Leads\Controllers;

    use CCM\Leads\Controllers\Controller as LeadsController;

    use Illuminate\Http\Request;

    class EsgController extends LeadsController
    {
        private $timeOut;

        function __construct() {
            $this->timeOut = config('esg.time_out');
        }

        public function accountMatching($email)
        {
            $response['success'] = false;

            if(config('esg.skip_call') === true)
            {
                lumenLog("skipped ESG call.");
                return $response;
            }

            try
            {
                $authResponse = $this->authentication();

                if ($authResponse['success'])
                    {
                    $authToken      = $authResponse['data']['access_token'];

                    $searchResponse = $this->search( ['email' => $email, 'auth_token' => $authToken] );

                    if ($searchResponse['success'])
                    {
                        $accountCode = $searchResponse['data'][0]['accountCode'];
                        $accountResponse                 = $this->account(['accountCode' => $accountCode, 'auth_token' => $authToken]);

                        if($accountResponse['success'])
                        {
                            $response['data'][0]['platform'] = 'ESG';
                            $response['data'][0]['account']  = $accountResponse['data'];
                            $response['data']                = json_encode($searchResponse['data']);
                            $response['success']             = true;
                        }
                    }
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
         * ESG authentication integration
         *
         * @return (GuzzleClient) $response
         */
        public function authentication()
        {
            $response = [ 'success' => false ];

            try
            {
                $method = config('esg.apis.authentication.method');
                $url    = config('esg.base_url').config('esg.apis.authentication.endpoint');
                $params = config('esg.apis.authentication.params');

                $request['multipart'] = Collect($params)->map(function($item, $key){
                    return [ 'name' => $key, 'contents' => $item ];
                })->toArray();

                $response = curlRequest($method, $url, $request, $this->timeOut);

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
         * ESG Search integration
         *
         * @params (array) $param
         * @return (GuzzleClient) $response
         */
        private function search($params)
        {
            lumenLog('search');
            $data = [ 'success' => false, 'data' => [] ];

            try
            {
                $method = config('esg.apis.search.method');
                $endPoint = config('esg.apis.search.endpoint');
                $endPoint = str_replace('{email}', $params['email'], $endPoint);
                $url = config('esg.base_url').$endPoint;

                $request['headers'] = [
                    'Authorization' => 'Bearer ' . $params['auth_token'],
                ];

                $response = curlRequest($method, $url, $request, $this->timeOut);

                if ($response != null && $response != null && $response->getStatusCode() == 200) {
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
         * ESG Account integration
         *
         * @params (array) $param
         * @return (GuzzleClient) $response
         */
        private function account($param)
        {
            $data = [ 'success' => false, 'data' => [] ];

            try
            {
                $method = config('esg.apis.account.method');
                $url = config('esg.base_url').config('esg.apis.account.endpoint');

                $request['query'] = ['accountCode' => $param['accountCode']];
                $request['headers'] = [
                    'Authorization' => 'Bearer ' . $param['auth_token'],
                ];

                $response = curlRequest($method, $url, $request, $this->timeOut);

                if ($response != null && $response->getStatusCode() == 200) {
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


    }
