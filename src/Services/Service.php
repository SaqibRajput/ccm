<?php

    namespace CCM\Leads\Services;

    use GuzzleHttp\Client;

    class Service
    {
        public function callExternalService($method, $requestUrl, $formParams = [], $headers = [])
        {
            try {
                $client = new Client([
                    'base_uri'  =>  $this->baseUri,
                ]);

                if(isset($this->secret))
                {
                    $headers['service-secret-token'] = $this->secret;
                }

                $response = $client->request($method, $requestUrl, [
                    'form_params' => $formParams,
                    'headers'     => $headers,
                ]);

                return $response->getBody()->getContents();
            }
            catch(\Exception $e) {
                lumenLog("---------------|Service Call failed |---------------");
                lumenLog($this->baseUri);
                lumenLog($this->secret);
                lumenLog($e->getMessage());
                lumenLog("---------------|Service Call failed |---------------");
            }
        }
    }
