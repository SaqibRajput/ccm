<?php

    /**
     * Developed by Saqib Rajput.
     * Email: rajput.saqib@hotmail.com
     * Mobile: 00-92-300-6710419
     * Date: 09/02/2022
     * Time: 11:14 AM
     */

    namespace CCM\Leads\Controllers;

    use CCM\Leads\Controllers\Controller as LeadsController;

    use App\Models\ZipCode;
    use App\Models\State;
    use CCM\Leads\Services\AuthenticationService;
    use Validator;
    use Illuminate\Http\Request;

    class BoomiController extends LeadsController
    {
        private $timeOut;

        function __construct() {
            $this->timeOut = config('boomi.time_out');
        }

        public function apiConfig($name, $url = '')
        {
            $url = config("boomi.base_url").config("boomi.apis.$name.endpoint").$url;
            $method = config("boomi.apis.$name.method");
            $params = config("boomi.apis.$name.params");

            return ["url" => $url, "method" => $method, "params" => $params ];
        }

        public function callToServer($name, $url = '')
        {
            if(config('boomi.skip_call') === true)
            {
                $message = 'Call to E1 server is skipped, please check configuration.';
                lumenLog($message);

                return ["success" => false, 'message' => $message];
            }

            try
            {
                $boomi = $this->apiConfig($name, $url);

                $apiResponse = curlRequest($boomi['method'], $boomi['url'], $boomi['params'], $this->timeOut);
                $encodedContent = $apiResponse->getBody()->getContents();
                $response =  collect(json_decode($encodedContent, true));

                return $response;
            }
            catch (\Exception $ex)
            {
                $message = $ex->getMessage().' '.$ex->getCode();
                lumenLog($message);

                return ["success" => false, 'message' => $message];
            }
            catch (\Throwable $t) {
                $message = $t->getMessage().' - '.$t->getCode();
                return ["success" => false, 'message' => $message];
            }

        }

        /**
         * Creates AB Number
         *
         * @param user_id
         * @return \Illuminate\Http\Response
         */

        public function createABNumber($user_id, $request_type, $management_user_id = null, $addressId = null)
        {

            \Log::info("E1StartCreatingAddressBook Start");
            \Log::info("E1StartCreatingAddressBook");

            $request = new Request();
            $request->merge([
                'user_id' => $user_id,
                'request_type' => $request_type,
            ]);

            $status_code = 200;
            $success = true;
            $message = '';
            $pagination = false;
            $entities = [];


            try {

                $user = User::find($request->user_id);
                $requestType = $request->has('request_type') ? $request->request_type : 'billing';

                //self::$requestLogUserId = $request->user_id;

                if (!$user)
                {
                    // message should be --> trans('account::messages.error.user_id_exists')
                    return createResponseData(self::STATUS_CODE_DB_FAILED, self::RESPONSE_FAILED, trans('main::message.error.db'));
                }

                $company = Company::find($user->company_id);


                if (!$company)
                {
                    // message should be --> trans('account::messages.error.company_not_found')
                    return createResponseData(self::STATUS_CODE_DB_FAILED, self::RESPONSE_FAILED, trans('main::message.error.db'));
                }

                //Fetch Addresses
                if($addressId)
                {
                    $addressOfUse   = Address::where('id', $addressId)->first();
                }
                else
                {
                    $addressOfUse   = Address::where('account_id', $company->id)->where('type', 'address of use')->first();
                }

                if (!$addressOfUse)
                {
                    // message should be --> trans('account::messages.error.address_not_found')
                    return createResponseData(self::STATUS_CODE_DB_FAILED, self::RESPONSE_FAILED, trans('main::message.error.db'));
                }

                $billingAddress = Address::where('account_id', $company->id)->where('type', 'billing')->first();


                $settingsObj = Setting::where('company_id',$company->id)->where('key','default_admin')->first();

                if($settingsObj)
                {
                    $authenticationService = new AuthenticationService();
//                    $intvitedUsers = $authenticationService->getServiceData('UserInvitation', [$where], [], $with);
//                    $defaultAdmin = User::find($settingsObj->value);
                }

                $taxExempt = null;
                $defaultAdmin = "";
                $pciFirstname = !empty($defaultAdmin) ? $defaultAdmin->first_name : $user->first_name;
                $pciLastname  = !empty($defaultAdmin) ? $defaultAdmin->last_name : $user->last_name;

                if($requestType == "address_of_use")
                {
                    $billingAddressType = "S";
                    $taxExempt = "S";
                    $addressObj = $addressOfUse;
                    $addressObjToUpdate = Address::find($addressOfUse->id);
                }
                else
                {
                    $billingAddressType = $this->isAddressesIdentical($billingAddress->toArray(), $addressOfUse->toArray()) ? "X" : "B";

                    $addressObj = $billingAddress;

                    // NEED to check why this query execute again
                    $addressObjToUpdate = Address::find($billingAddress->id);

                    if($requestType == "credit_card")
                    {
                        // NEED to create another service of payment
                        $creditCardObj = CreditCard::where('company_id',$company->id)->first();
                        $pciFirstname = !empty($creditCardObj) ? decryptString($creditCardObj->name) : null;
                    }
                }

                switch ($company->sales_division)
                {
                    case 'ESG':
                        $businessUnit = 8;
                        break;

                    case 'PSG':
                        $businessUnit = 5;
                        break;

                    case 'BSG':
                        $businessUnit = 1;
                        break;

                    default :
                        $businessUnit = 1;
                }

                $paramsForABCall = $this->setCreateABParams([
                    'billingAddressType' => $billingAddressType,
                    'businessUnit' => $businessUnit,
                    'company' => $company,
                    'addressObj' => $addressObj,
                    'pciFirstname' => $pciFirstname,
                    '$pciLastname' => $pciLastname,
                    '$taxExempt' => $taxExempt,
                    '$user' => $user,
                ]);

                $createABCall = $this->createABCall($paramsForABCall);

                if ($createABCall['status_code'] != 200)
                {
                    $status_code = 422;
                    $success = false;
                    $message = trans('account::messages.error.server_error');
                    $params=[];
                    $params['user_id']=$user_id;
                    $params['request_type']=$request_type;
                    $response = [
                        'params' => json_encode($params),
                        'endpoint' => config('e1apis.base_url').config('e1apis.apis.create_address_book.endpoint'),
                        'method' => config('e1apis.apis.create_address_book.method'),
                        'provider_response' => $createABCall['data'],
                        'request_map' => '',
                        'response_map' => '',
                        'date' => Carbon::now()->toDateString(),
                        'time' => Carbon::now()->toTimeString(),
                    ];
                    throw new InvalidResponseException(null,$response);
                } else {
                    $addressBookNumber = $createABCall['data']['addressBook'];

                    if($billingAddressType == 'S'){
                        // Update STAB in the database
                        $addressObjToUpdate->stab = $addressBookNumber;
                        $addressObjToUpdate->save();
                    }
                    else{
                        // Update the AB Number in the database
                        $company->address_book_no = $addressBookNumber;

                        if ($company->sales_division == 'ESG') {
                            $company->parent_ab_number = $addressBookNumber;
                        }

                        $company->save();
                    }

                    $ordersController = new OrderController();
                    // Calling Create Provision for Non Final Status Subscription(s)
                    $ordersController->createProvisionCallForNonFinalStatus($user->company_id);

                    $entities = $createABCall['data'];
                    $message = 'AB Number updated successfully';
                }

            } catch (InvalidResponseException $e) {

                $status_code = 422;
                $success = false;
                $message = trans('account::messages.error.server_error');

                $rbmq_message = [
                    'user_id' => $user_id,
                    'request_type' => $request_type
                ];

                // Add data to subscriptions Table
                $data = [
                    'integration_point'     => 'e1-create-address-book',
                    'data'                  => serialize(array($e->getData())),
                    'company_id'            => $company->id,
                    'user_id'               => empty($management_user_id)?$user->id:$management_user_id,
                    'date'                  => Carbon::now()->toDateString(),
                    'time'                  => Carbon::now()->toTimeString(),
                    'entity'                => 'Address',
                    'entity_id'             => $addressObj->id,
                    'created_at'            => Carbon::now()->toDateTimeString(),
                    'updated_at'            => Carbon::now()->toDateTimeString(),
                    'message'               => serialize($rbmq_message),
                ];

                // Add data to subscriptions Table
                $E1FailureResponseId = E1FailureResponse::insertGetId($data);

                \Log::error("Create AB Number Error ID #: " . $E1FailureResponseId);
            } catch (\Exception $e) {
                $status_code = 422;
                $success = false;
                $message = trans('account::messages.error.server_error');
            } catch (\Throwable $t) {
                $status_code = 422;
                $success = false;
                $message = trans('account::messages.error.server_error');
            }

            return createResponseData($status_code, $success, $message, $entities, $pagination, $request);
        }

        /*
         * To check whether given addresses are identical or not
         *
         * @params object $address1,$address2
         * @param boolean $forUpdateAddress (default false)
         * @return boolean true/false
         */
        public function isAddressesIdentical($address1, $address2, $forUpdateAddress = false)
        {
            if(array_key_exists('id',$address1))
            {
                unset($address1['id']);
            }

            if(array_key_exists('id',$address2))
            {
                unset($address2['id']);
            }

            if(array_key_exists('organization_name',$address1))
            {
                unset($address1['organization_name']);
            }

            if(array_key_exists('organization_name',$address2))
            {
                unset($address2['organization_name']);
            }

            if(array_key_exists('type',$address1))
            {
                unset($address1['type']);
            }

            if(array_key_exists('type',$address2))
            {
                unset($address2['type']);
            }

            if(array_key_exists('created_at',$address1))
            {
                unset($address1['created_at']);
            }

            if(array_key_exists('created_at',$address2))
            {
                unset($address2['created_at']);
            }

            if(array_key_exists('updated_at',$address1))
            {
                unset($address1['updated_at']);
            }

            if(array_key_exists('updated_at',$address2))
            {
                unset($address2['updated_at']);
            }

            if(array_key_exists('duns_number',$address1))
            {
                unset($address1['duns_number']);
            }

            if(array_key_exists('duns_number',$address2))
            {
                unset($address2['duns_number']);
            }

            if(array_key_exists('stab',$address1))
            {
                unset($address1['stab']);
                unset($address2['stab']);
            }

            if($forUpdateAddress)
            {
                if(array_key_exists('phone',$address1) || array_key_exists('ext',$address1))
                {
                    unset($address1['phone']);
                    unset($address1['ext']);
                }

                if(array_key_exists('phone',$address2) || array_key_exists('ext',$address2))
                {
                    unset($address2['phone']);
                    unset($address2['ext']);
                }
            }

            return empty(array_diff($address1 ,$address2)) ? true : false;
        }

        function setCreateABParams($param)
        {
            $billingAddressType = $param['billingAddressType'];
            $businessUnit = $param['businessUnit'];
            $company = $param['company'];
            $addressObj = $param['addressObj'];
            $pciFirstname = $param['pciFirstname'];
            $pciLastname = $param['$pciLastname'];
            $taxExempt = $param['$taxExempt'];
            $user = $param['$user'];

            $paramsForABCall = [
                "billingAddressType" => $billingAddressType,
                "businessUnit" => $businessUnit,
                "companyName" => $company->name,
                "address" => [
                    "attention" => null,
                    "city" => $addressObj->city,
                    "country" => "US",
                    "county" => null,
                    "state" => $addressObj->state()->first()->name,
                    "streetAddress" => $addressObj->address_line_1,
                    "streetAddress2" => $addressObj->address_line_2,
                    "streetAddress3" => null,
                    "streetAddress4" => null,
                    "zipCode" => $addressObj->zip_code,
                ],
                "primaryContactInfo" => [
                    "type" => "C",
                    "emails" => null,
                    "firstName" => $pciFirstname,
                    "lastName" => $pciLastname,
                    "middleName" => null,
                    "name" => $company->name,
                ],
                "phones" => [
                    "number" => (count(explode("-",$addressObj->phone)) > 3) ? (explode("-",$addressObj->phone)[1] ."-". explode("-",$addressObj->phone)[2] ."-". explode("-",$addressObj->phone)[3]) : '',
                    "type" => "D",
                    "prefix" => (count(explode("-",$addressObj->phone)) > 0) ? explode("-",$addressObj->phone)[0] : '+1',
                ],
                "relatedAddressBooks" => [
                    "billingInfo" => [
                        "ApplyFreight" => "Y" ,
                        "backordersAllowed" => "Y",
                        "customerPORequired" => "N",
                        "holdOrdersCode" => null,
                        "invoiceComplete" => "N",
                        "partialOrderShipmentsAllowed" => "Y",
                        "partialShipmentsAllowed" => "N",
                        "taxExempt" => $taxExempt,
                    ],
                    "parent" => [
                        "addressBook" => null,
                        "sourceID" => null,
                    ],
                    "billTo" => [
                        "addressBook" => null,
                        "sourceID" => null,
                    ],
                    "categoryCodes" => [
                        "categoryCode01" => " ",
                        "categoryCode02" => " ",
                        "categoryCode03" => " ",
                        "categoryCode04" => " ",
                        "categoryCode05" => "06",
                        "categoryCode06" => " ",
                        "categoryCode07" => " ",
                        "categoryCode08" => " ",
                        "categoryCode09" => " ",
                        "categoryCode10" => " ",
                        "categoryCode11" => " ",
                        "categoryCode12" => " ",
                        "categoryCode13" => " ",
                        "categoryCode14" => " ",
                        "categoryCode15" => " ",
                        "categoryCode16" => " ",
                        "categoryCode17" => "NJ",
                        "categoryCode18" => " ",
                        "categoryCode19" => " ",
                        "categoryCode20" => " ",
                        "categoryCode21" => " ",
                        "categoryCode22" => " ",
                        "categoryCode23" => " ",
                        "categoryCode24" => " ",
                        "categoryCode25" => " ",
                        "categoryCode26" => " ",
                        "categoryCode27" => "Y",
                        "categoryCode28" => " ",
                        "categoryCode29" => " ",
                        "categoryCode30" => " "
                    ],
                    "classificationCodes" => [
                        "classificationCode01" => " ",
                        "classificationCode02" => " ",
                        "classificationCode03" => " ",
                        "classificationCode04" => " ",
                        "classificationCode05" => " "
                    ],
                    "contacts" => [
                        [
                            "type" => "B",
                            "emails" => null,
                            "firstName" => $user->first_name,
                            "lastName" => $user->last_name,
                            "middleName" => null,
                            "name" => $user->first_name . " " . $user->last_name,
                            "title" => null,
                            "phones" => [
                                [
                                    "number" => (count(explode("-",$addressObj->phone)) > 3) ? (explode("-",$addressObj->phone)[1] ."-". explode("-",$addressObj->phone)[2] ."-". explode("-",$addressObj->phone)[3]) : '',
                                    "type" => "D",
                                    "prefix" => (count(explode("-",$addressObj->phone)) > 0) ? explode("-",$addressObj->phone)[0] : '+1'
                                ]
                            ]
                        ]
                    ],
                    "customerInfo" => [
                        "collectorManagerCode" => null,
                        "paymentTerms" => null,
                        "salesRep" => [
                            "addressBook" => null,
                            "sourceId" => null,
                        ],
                    ],
                    "parent" => null,
                ],
                "searchType" => "C",
                "sourceAuditId" => null,
                "sourceId" => null,
                "SIC" => null,
            ];

        }




        // ======================= IT SHOULB BE MOVED IN ANOTHER CONTROLLER
        // ======================= IN THIS CLASS ONLY BOOMI CALLS

        /**
         *
         * return array of array containing city and state from zip code
         *
         * @param Request $request
         *
         * @return array
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public function getZipCode($zipCode)
        {
            // to removed validation if not required in future.
            $request = new Request();
            $request->merge(['zip_code' => $zipCode]);

            $validator = Validator::make($request->all(), [
                'zip_code' => ['required','min:5','regex:/^[0-9]{5}(\s[0-9]{4})?$/'],
            ], $this->validationMessages());

            if ($validator->fails())
            {
                return createResponseData(self::STATUS_CODE_VALIDATION_FAILED, self::RESPONSE_FAILED, $validator->errors());
            }

            try
            {
                $zip_code = substr($zipCode,0, 5);

                if (!$this->isValidUSZipCode($zip_code))
                {
                    return createResponseData(self::STATUS_CODE_VALIDATION_FAILED, self::RESPONSE_FAILED, trans("message.zip_code_invalid"));
                }

                $response = $this->callToServer('zip_code', $zip_code);

                if($response['success'] == false)
                {
                    return createResponseData(self::STATUS_CODE_CURL_FAILED, self::RESPONSE_FAILED, $response['message']);
                }

                if($response->has('ERROR') || !$response->has('postalCodeDetails') || count(optional($response)['postalCodeDetails']) == 0)
                {
                    return createResponseData(self::STATUS_CODE_FAILED, self::RESPONSE_FAILED, trans("message.zip_code_invalid"));
                }

                $db_states = State::all();
                $postal_code_details = collect(optional($response)['postalCodeDetails']);

                $cities = [];
                $states = collect([]);
                $zip_codes = [];
                $countries = [];

                $postal_code_details = $postal_code_details->filter(function($value, $index) {
                    return $value['country'] == "US";
                });

                $postal_code_details->each(function($data) use (&$cities, &$states, &$zip_codes, &$countries, $db_states) {

                    if(!in_array(optional($data)['city'], $cities))
                    {
                        $cities[] = ucwords(strtolower(optional($data)['city']));
                    }

                    if(optional(collect(optional(optional($states)->where('name', optional($data)['state']))->first()))->has(['id', 'name']) === false)
                    {
                        $filtered = $db_states->where('name', optional($data)['state'])->first();

                        if(!(optional($filtered)->id !== null && optional($filtered)->id !== ""))
                        {
                            $filtered = State::create(['name' => optional($data)['state']]);
                        }

                        $state = new \StdClass();
                        $state->id = $filtered->id;
                        $state->name = strtoupper(optional($data)['state']);
                        $states[] = $state;
                    }

                    if(!in_array(optional($data)['postalCode'], $zip_codes))
                    {
                        $zip_codes[] = optional($data)['postalCode'];
                    }

                    if(!in_array(optional($data)['country'], $countries))
                    {
                        $countries[] = optional($data)['country'];
                    }

                });

                $data = [
                    'states' => $states->toArray(),
                    'cities' => $cities,
                    'zip_codes' => $zip_codes,
                    'countries' => $countries,
                ];

                return createResponseData(self::STATUS_CODE_SUCCESS, self::RESPONSE_SUCCESS, trans("message.zip_code_success"), $data);

            }
            catch (\GuzzleException $t)
            {
                $message = $t->getMessage().' - '.$t->getCode();
                return createResponseData(self::STATUS_CODE_FAILED, self::RESPONSE_FAILED, $message);
            }

        }

        function isValidUSZipCode($zipcode)
        {
            $isValid = false;

            try {

                if (ZipCode::where('postal_code', $zipcode)->count() > 0)
                {
                    $isValid = true;
                }
            }
            catch (\Exception $e)
            {
                lumenLog("Exception *isValidUSZipCode*: " . $e->getMessage());
            }

            return $isValid;
        }



    }
