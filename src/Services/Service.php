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
            $this->timeOut = env("SERVICE_TIME_OUT", 3);
        }

        public function callOtherService($method, $requestUrl, $formParams = [], $headers = [])
        {
            $response = null;

            try {
                $client = new Client([
                    'base_uri' => $this->baseUri
                ]);

                if(isset($this->secret))
                {
                    $headers['service-secret-token'] = $this->secret;
                }

                $clientReaponse = $client->request($method, $requestUrl, [
                    'form_params' => $formParams,
                    'headers'     => $headers,
                ]);
                echo '<pre>';print_r($clientReaponse);echo '</pre>'; die('-----');

                $response = $clientReaponse->getBody()->getContents();
                $response['success'] = true;
            }
            catch(Exception $ex)
            {
                $response['success'] = false;
                $response['exception'] = get_class($ex);
                $response['message'] = $ex->getMessage();
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
