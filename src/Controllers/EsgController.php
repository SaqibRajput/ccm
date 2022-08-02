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
use GuzzleHttp\Exception\GuzzleException;
use PhpOffice\PhpSpreadsheet\Shared\OLE\PPS;

class EsgController extends LeadsController {

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

    /**
     * ESG Product ID integration     
     * @params (array) $param
     * @return (GuzzleClient) $response
     */
    private function getProductId($param)
    {
            
        $data = [ 'success' => false, 'data' => [] ]; 
        $url = config('esg.base_url').config('esg.apis.productId.endpoint');
        $url = str_replace('{suppPart}', $param['sku'], $url);
        
        $request['headers'] = [
            'Authorization' => 'Bearer ' . $param['auth_token'],
        ];

        $method = config('esg.apis.productId.method');

        try {
            //get api response
            $response = curlRequest($method, $url, $request);
            
            //check if status 200
            if ($response->getStatusCode() == 200) {
                $data['success'] = true;
                // $data['data'] = $response->getBody()->getContents();
                $data['data'] = json_decode($response->getBody()->getContents(), true);
                
            }
        } //to handle guzzle exception
        catch (GuzzleException $t) {
            $data['statusCode'] = (method_exists($t, 'getCode') ? $t->getCode() : 500);
            $data['message'] = $t->getMessage();

            // setApiLog($method, $url, $data, $request, $auth_request);
        }
            
        return $data;
    }

    private function getProductPrice($param)
    {

        $data = [ 'success' => false, 'data' => [] ];

        $method = config('esg.apis.price.method');
        $url = config('esg.base_url').config('esg.apis.price.endpoint');
        $url = str_replace('{accountCode}', $param['account_code'], $url);
        $url = str_replace('{supplierPartId}', $param['supplier_part_id'], $url);
        

        $request['headers'] = [
            'Authorization' => 'Bearer ' . $param['auth_token'],
        ];

        try {
            //get api response
            $response = curlRequest($method, $url, $request);

            //check if status 200
            if ($response->getStatusCode() == 200) {
                $data['success'] = true;
                $data['data'] = $response->getBody()->getContents();
            } 
                
        } //to handle guzzle exception
        catch (GuzzleException $t) {
            $data['statusCode'] = (method_exists($t, 'getCode') ? $t->getCode() : 500);
            $data['message'] = $t->getMessage();

            // setApiLog($method, $url, $data, $request, $auth_request);
        } 
            
        return $data; 
    }

    /**
     * Get Price Call from db or ESG 
     * @params $request
     * @return (GuzzleClient) $response
     */    
    public function getPriceCall($request)
    {    
        try
        {   
            if($request->get('withDatabaseCall', false) == true)
            {
                // fetch data from database if e1 rurn error or empty.
                // $data = $this->getPriceCallwithDatabaseCall($request);
            }
            else
            {
                $data = $this->getPrice($request);            
              
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
     * ESG Price Call 
     * @params $request
     * @return (GuzzleClient) $response
     */

    function getPrice($request) {

        $response['success'] = false;

        if(config('esg.skip_call') === true)
        {
            lumenLog("skipped ESG call.");
            return $response;
        }
         
       
        $authResponse = $this->authentication();
        
        if ($authResponse['success'])
        {
            $sku  = $request->get('skus');           

            $authToken      = $authResponse['data']['access_token'];
            
            $productIdResponse = $this->getProductId( ['sku' => $sku, 'auth_token' => $authToken] );
            
            if ($productIdResponse['success'])
            {
                // need to be fixed it will be get from database in account matching 
                $accountCode = 'BUS019';
                $supplier_part_id = $productIdResponse['data']['id'];
              
                $productPriceResponse = $this->getProductPrice(['account_code' => $accountCode, 'supplier_part_id' => $supplier_part_id, 'auth_token' => $authToken]);
                 
                if($productPriceResponse['success'])
                {     
                
                    $priceResponse = json_decode($productPriceResponse['data'], true);

                    $item['from']  = "esg";
                    $item['retry'] =  false;
                    $item['isValid'] =  true;
                    $item['adjPrice'] = $priceResponse['price'];
                    $item['item']     = $sku;
                    
                    $response['data'] = [$item];
                    $response['success'] = true; 
                    $response['status_code'] = 200;  

                } 
            }
        }
        
        return $response;
        
    }


}
