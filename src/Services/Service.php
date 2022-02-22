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

            try
            {
                $timeOutArray = [
                    'timeout' => $this->timeOut,
                    'connect_timeout' => $this->timeOut
                ];

                $endPoint = $this->baseUri.$requestUrl;
                $headers['service-secret-token'] = $this->secret;

                $request = [
                    'form_params' => $formParams,
                    'headers'     => $headers
                ];

                $client   = new Client($timeOutArray);
                $curlResponse = $client->request($method, $endPoint, $request);

                $content = $curlResponse->getBody()->getContents();

                // check response is json to decode
                $response = (isJson($content)) ? json_decode($content, true) : $content;
            }
            catch (\Exception $ex)
            {
                // response set as null to handle same in all project.
                $response = null;

                lumenLog('Exception: callOtherService : Start');
                lumenLog('$timeOut: '.$this->timeOut);
                lumenLog('$method: '.$method);
                lumenLog('$endPoint: '.$endPoint);
                lumenLog('$params: '.json_encode($request));
                lumenLog($ex->getLine().' - '.$ex->getMessage());
                lumenLog('Exception: callOtherService : End');
            }

            return $response;
        }

        public function getServiceData($model, $where, $columns = [], $with = [], $first = false)
        {
            $param = [
                'model' => $model,
                'columns' => $columns,
                'where' => $where,
                'with' => $with,
            ];

            return $this->callOtherService('POST', '/service/get-service-data', $param);
        }

        public function createServiceData($model, $data)
        {
            $param = [
                'model' => $model,
                'data' => $data
            ];

            return $this->callOtherService('POST', '/service/create-service-data', $param);
        }

        public function updateServiceData($model, $data, $where)
        {
            $param = [
                'model' => $model,
                'data' => $data,
                'where' => $where
            ];

            return $this->callOtherService('POST', '/service/update-service-data', $param);
        }

        public function deleteServiceData($model, $data)
        {
            $param = [
                'model' => $model,
                'data' => $data
            ];

            return $this->callOtherService('POST', '/service/delete-service-data', $param);
        }
    }
