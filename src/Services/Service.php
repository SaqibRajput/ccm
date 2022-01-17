<?php

    namespace CCM\Leads\Services;

    use GuzzleHttp\Client;
    use Illuminate\Http\Request;

    use Exception;
    use GuzzleHttp\Exception\GuzzleException;
    use GuzzleHttp\Exception\ClientException;

    class Service
    {
        public $timeOut;
        public $secret;

        public function __construct()
        {
            $this->timeOut = env("SERVICE_TIME_OUT", 30);
        }

        public function callOtherService($method, $requestUrl, $formParams = [], $headers = [])
        {
            $response = null;

            try {

                if(isset($this->secret))
                {
                    $headers['service-secret-token'] = $this->secret;
                }

                $client = new Client();
                $clientReaponse = $client->request($method, $this->baseUri.$requestUrl, [
                    'form_params' => $formParams,
                    'headers'     => $headers
                ]);

                $response = $clientReaponse->getBody()->getContents();
            }
            catch(Exception $ex)
            {
                $response['success'] = false;
                $response['exception'] = get_class($ex);
                $response['message'] = $ex->getMessage();
                $response['message'] = $ex->getTrace();
                lumenLog("---------------|Service Call failed |---------------");
                lumenLog(get_class($ex));
                lumenLog($this->baseUri);
                lumenLog($this->secret);
                lumenLog($ex->getMessage());
                lumenLog("---------------|Service Call failed |---------------");
            }
            finally
            {
                return $response;
            }
        }
    }
