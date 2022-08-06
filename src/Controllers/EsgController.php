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
use Component\CatalogComponent\App\Http\Controllers\DatabasePriceController;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use PhpOffice\PhpSpreadsheet\Shared\OLE\PPS;
use Throwable;

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
        $response = [ 'success' => false , 'status_code' => 401];

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
     * Get Price Call from db or ESG 
     * @params $request
     * @return (GuzzleClient) $response
     * its the starting function where we decide database required or not
     */    
    public function priceCall($request)
    {    
      
        $data = [];
        try
        {   
            if($request->get('withDatabaseCall', false) == true)
            {   
               
                // fetch price from database if e1 rurn error or empty. 
                $data = $this->priceCallwithDatabase($request);
            }
            else
            {     
                // fetch price from ESG
                $data = $this->price($request);  
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
     * in this function we handled all the price related third party calls
     */

    function price($request) {

        $response = [ 'success' => false , 'status_code' => 401];

        if(config('esg.skip_call') === true)
        {
            // needs to handle this case in both cases fail/pass
            // $data = ['from' => "esg", 'retry' =>  true, 'isValid' =>  false, 'adjPrice' => 0.00, 'item' => $request->get('skus')];
            // $response = [ 'success' => false , 'status_code' => 401, 'data' => [$data]];

            lumenLog("skipped ESG call.");
            return $response;
        }
                
        $sku  = $request->get('skus');    

         

        $authResponse = $this->authentication();  
        
        if ($authResponse['success'])
        {
            $authToken = $authResponse['data']['access_token'];
            
            $productIdResponse = $this->productId( ['sku' => $sku, 'auth_token' => $authToken]);  
           

            if ($productIdResponse['success'] && $productIdResponse['data']['onCatalog'])
            {
                // need to be fixed it will be get from database in account matching 
                // $accountCode = 'BUS019';
                $accountCode = $request->accountCode;
                $supplier_part_id = $productIdResponse['data']['id'];
              
                $productPriceResponse = $this->productPrice(['account_code' => $accountCode, 'supplier_part_id' => $supplier_part_id, 'auth_token' => $authToken]);
                
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
        } else {

            $item['from']  = "esg";
            $item['retry'] =  true;
            $item['isValid'] =  false;
            $item['adjPrice'] = 0.00;
            $item['item'] = $sku;
            

            $response['data'] = [$item];
            $response['success'] = true;
            $response['status_code'] = 200; 
            
        }
         
        return $response;
        
    }

    /**
     * ESG Product ID integration     
     * @params (array) $param
     * @return (GuzzleClient) $response
     * its the single call to server to get product id
     */
    private function productId($param)
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

    /**
     * ESG Product ID integration     
     * @params (array) $param
     * @return (GuzzleClient) $response
     * its the single call to server to get price against product id
     */
    private function productPrice($param)
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
     * ESG Price Call with DB
     * @params $request
     * @return (GuzzleClient) $response
     * its a database call to get price if the third party server call is failed.
    */

    public function priceCallwithDatabase($request) {
       
      $data = $this->price($request);
       
      //if esg Api return false
      if(!empty($data['data']) && $data['data'][0]['isValid'] == false) {
        $sku = $request->get('skus');
        $response['success'] = true;
        $response['status_code'] = 200;
        
        $dbPriceInst = new DatabasePriceController(); 
        $response['data'] = $dbPriceInst->getPriceFromDb($sku);  // get Price from Database     
      }
      else { 
        $response = $data; 
      } 

      return $response;
    }

    /**
     * SKU check in ESG product list API
     * @param $sku_id
     * @param $auth_token
     * @return mixed|null
     */
    private function checkSkuExistInE1($sku_id, $auth_token)
    {
        $response = false;
        try {
            $esg_baseurl = config('accountmatching.esg.base_url');
            $endpoint = config('accountmatching.esg.apis.product_sku.endpoint');
            $method = config('accountmatching.esg.apis.product_sku.method');

            $url = $esg_baseurl . str_replace('{sku_id}', $sku_id, $endpoint);

            //set authorization headers
            $request['headers'] = [
                'Authorization' => 'Bearer ' . $auth_token,
            ];

            $curlResponse = curlRequest($method, $url, $request);
            if ($curlResponse && $curlResponse->getStatusCode() == 200) {
                $response = json_decode($curlResponse->getBody()->getContents(), true);
            }
        } catch (Throwable $e) {
            \Log::info('ESG SKU API Error: ' . $e->getMessage());
        }
        return $response;
    }


    /**
     * Add Bill Line items in ESG Cart API
     * @param $accountId
     * @param $auth_token
     * @param $productId
     * @param int $productQty
     * @return mixed|null
     */
    private function addItemInCart($accountId, $auth_token, $productId, int $productQty = 1)
    {
        $response = false;
        try {
            $esg_baseurl = config('accountmatching.esg.base_url');
            $endpoint = config('accountmatching.esg.apis.add_to_cart.endpoint');
            $method = config('accountmatching.esg.apis.add_to_cart.method');

            $url = $esg_baseurl . str_replace('{account_id}', $accountId, $endpoint);

            //set authorization headers
            $request['headers'] = [
                'Authorization' => 'Bearer ' . $auth_token,
            ];

            $request['json'] = [
                'productId' => $productId,
                'productType' => 'PRODUCT',
                'quantity' => $productQty
            ];

            $curlResponse = curlRequest($method, $url, $request);
            if ($curlResponse && $curlResponse->getStatusCode() == 201) {
                $response = json_decode($curlResponse->getBody()->getContents(), true);
            }
        } catch (Throwable $e) {
            \Log::info('Add Cart Item API Error: ' . $e->getMessage());
        }
        return $response;
    }


    /**
     * Get ESG Cart API
     * @param $accountId
     * @param $auth_token
     * @return mixed|null
     */
    private function getCart($accountId, $auth_token)
    {
        $response = false;
        try {
            $esg_baseurl = config('accountmatching.esg.base_url');
            $endpoint = config('accountmatching.esg.apis.user_cart.endpoint');
            $method = config('accountmatching.esg.apis.user_cart.method');

            $url = $esg_baseurl . str_replace('{account_id}', $accountId, $endpoint);

            //set authorization headers
            $request['headers'] = [
                'Authorization' => 'Bearer ' . $auth_token,
            ];

            $curlResponse = curlRequest($method, $url, $request);
            if ($curlResponse && $curlResponse->getStatusCode() == 200) {
                $response = json_decode($curlResponse->getBody()->getContents(), true);
            }
        } catch (Throwable $e) {
            \Log::info('Get Cart API Error: ' . $e->getMessage());
        }
        return $response;
    }


    /**
     * Create Order in ESG API
     * @param $accountId
     * @param $auth_token
     * @param $params
     * @return mixed|null
     */
    private function createOrder($accountId, $auth_token, $params)
    {
        $response = false;
        try {
            $esg_baseurl = config('accountmatching.esg.base_url');
            $endpoint = config('accountmatching.esg.apis.create_order.endpoint');
            $method = config('accountmatching.esg.apis.create_order.method');

            $url = $esg_baseurl . str_replace('{account_id}', $accountId, $endpoint);

            //set authorization headers
            $request['headers'] = [
                'Authorization' => 'Bearer ' . $auth_token,
            ];

            $request['json'] = $params;

            $curlResponse = curlRequest($method, $url, $request);
            if ($curlResponse && $curlResponse->getStatusCode() == 201) {
                $response = json_decode($curlResponse->getBody()->getContents(), true);
            }
        } catch (Throwable $e) {
            \Log::info('Create Order API Error: ' . $e->getMessage());
        }
        return $response;
    }


    /**
     * Get ESG User Address API
     * @param $accountCode
     * @param $auth_token
     * @return mixed|null
     */
    private function getAddressESGUser($accountCode, $auth_token)
    {
        $response = false;
        try {
            $esg_baseurl = config('esg.base_url');
            $endpoint = config('esg.apis.user_ship_address.endpoint');
            $method = config('esg.apis.user_ship_address.method');
             
            $url = $esg_baseurl . str_replace('{account_code}', $accountCode, $endpoint);
           
            //set authorization headers
            $request['headers'] = [
                'Authorization' => 'Bearer ' . $auth_token,
            ];
             
            $curlResponse = curlRequest($method, $url, $request);
             
            if ($curlResponse && $curlResponse->getStatusCode() == 200) {
              
                $response = json_decode($curlResponse->getBody()->getContents(), true);
            }
        } catch (Throwable $e) {
            \Log::info('ESG User Address API Error: ' . $e->getMessage());
        }
        return $response;
    }

    /**
     * Clear ESG Cart API
     * @param $accountId
     * @param $auth_token
     * @return mixed|null
     */
    private function deleteCart($accountId, $auth_token)
    {   
        $response = false;
        try {
            $esg_baseurl = config('accountmatching.esg.base_url');
            $endpoint = config('accountmatching.esg.apis.clear_cart.endpoint');
            $method = config('accountmatching.esg.apis.clear_cart.method');

            $url = $esg_baseurl . str_replace('{account_id}', $accountId, $endpoint);

            //set authorization headers
            $request['headers'] = [
                'Authorization' => 'Bearer ' . $auth_token,
            ];
           
            $curlResponse = curlRequest($method, $url, $request);
            dd($curlResponse);
            if ($curlResponse && $curlResponse->getStatusCode() == 200) {
                $response = json_decode($curlResponse->getBody()->getContents(), true);
            }
        } catch (Throwable $e) {
            \Log::info('Delete Cart API Error: ' . $e->getMessage());
        }
        return $response;
    }


    function ESGOrderCreationCall($bills) {
        
       

        $authResponse = $this->authentication();   

        if ($authResponse['success']) {
            $body = $authResponse['data'];
            $access_token = $body['access_token'];  

            foreach ($bills as $bill) {
                $company_data = $bill->company; // get params: esg_account_code, esg_account_id 
                // $esg_user_address = $this->getAddressESGUser($company_data->esg_account_code, $access_token);
                $esg_user_address = $this->getAddressESGUser('BUS019', $access_token);
              
                if (!$esg_user_address) {
                    $bill->order_status = 2;
                    $bill->error_response = "Error in ESG user address retrieving!";
                    $bill->save();
                    \Log::info('Error: in getAddressESGUser method than skip bill');
                    continue; // if esg user address not exist in esg portal than skip bill
                }
                 
                $shipToId = collect($esg_user_address)->first()['shipToId'];   
                 
                // clear cart first and add bill line items add into cart
                // $this->deleteCart($company_data->esg_account_id, $access_token);
                
                $this->deleteCart(801, $access_token);

                foreach ($bill->billLines as $bill_line) {
                    $subscription = $bill_line->subscription;
                    if (is_null($subscription)) { // if bill line item subscription not found than skip bill
                        $bill->order_status = 2;
                        $bill->error_response = "Error in BillLine subscription not found!";
                        $bill->save();
                        \Log::info('Error: in bill_line->subscription variable than skip bill');
                        continue 2;
                    }

                    $sku_id = $subscription->service->skuid;

                    $sku_check_resp = $this->checkSkuExistInE1($sku_id, $access_token);
                    if (!$sku_check_resp) { // if SKU not exist in ESG api than skip bill
                        $bill->order_status = 2;
                        $bill->error_response = "Error in ESG SKU_ID retrieving data!";
                        $bill->save();
                        \Log::info('Error: in checkSkuExistInE1 method than skip bill');
                        continue 2;
                    }

                    $this->addItemInCart($company_data->esg_account_id, $access_token, $sku_check_resp['id'], $bill_line->Seats);
                }

                $cartDetail = $this->getCart($company_data->esg_account_id, $access_token);
                if (!$cartDetail) { // cart response empty or null order not placed
                    $bill->order_status = 2;
                    $bill->error_response = "Error in ESG item into add to cart!";
                    $bill->save();
                    \Log::info('Error: in getCart method than skip bill');
                    continue;
                }
                $cartHeaderId = $cartDetail['cartHeaderId'];

                $resp = $this->createOrder($company_data->esg_account_id, $access_token, [
                    "attention" => substr($company_data->name, 0, 30),
                    "po" => "CCM." . Carbon::now()->format('YmdHis'),
                    "shipToId" => $shipToId,
                    "sourceId" => $cartHeaderId,
                    "sourceType" => "CART"
                ]);
                if (!$resp) {
                    $bill->order_status = 2;
                    $bill->error_response = "Error in ESG order create process!";
                    $bill->save();
                    \Log::info('Error: in createOrder method than skip bill');
                    continue;
                }

                $bill->order_status = 1;
                $bill->response = json_encode($resp);
                $bill->esg_order_number = $resp['orderNumber'];
                $bill->save();

                \Log::info('ESG Order Created: ' . print_r(json_encode($resp), 1));
            }
        }
    }


}
