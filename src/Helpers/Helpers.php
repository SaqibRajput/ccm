<?php

    use GuzzleHttp\Client;
    use Illuminate\Http\Request;

    function performRequest($method, $requestUrl, $formParams = [], $headers = [])
    {
        $client = new Client([
            'base_uri'  =>  $this->baseUri,
        ]);

        if(isset($this->secret))
        {
            $headers['Authorization'] = $this->secret;
        }

        $response = $client->request($method, $requestUrl, [
            'form_params' => $formParams,
            'headers'     => $headers,
        ]);

        return $response->getBody()->getContents();
    }

    if (! function_exists('createResponseData'))
    {
        function createResponseData($code, $success, $message = '', $data = [], $pagination = false, $request = false, $skip = false)
        {
            $response = [];
            if (!$skip)
            {
                if (gettype($message) == 'object')
                {
                    $message = collect($message->getMessages())->map(function ($item) {
                        return collect($item)->map(function ($item) {
                            $item = preg_replace("/(\.|,)$/", "", $item);
                            $item = ucfirst(str_replace(' id ', ' ID ', $item));
                            $item = preg_replace_callback('/[.!?].*?\w/', function ($matches) { return strtoupper($matches[0]); }, $item);

                            return preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) { return strtolower($matches[0]); }, $item);
                        })->toArray();
                    });
                }
                else if (gettype($message) == 'array')
                {
                    $new_message = (object)[];
                    foreach ($message as $index => $item)
                    {
                        foreach ($item as $key => $value)
                        {
                            $value                   = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $value))));
                            $value                   = preg_replace_callback('/[.!?].*?\w/', function ($matches) { return strtoupper($matches[0]); }, $value);
                            $new_message->{$index}[] = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) { return strtolower($matches[0]); }, $value);
                        }
                    }
                    $message = $new_message;
                }
                else
                {
                    if (is_array($message))
                    {
                        $messages = $message;
                        $message  = [];
                        foreach ($messages as $msg)
                        {
                            if (is_array($msg))
                            {
                                $msgs = $msg;
                                foreach ($msgs as $m)
                                {
                                    $message = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $m))));
                                    $message = preg_replace_callback('/[.!?].*?\w/', function ($matches) { return strtoupper($matches[0]); }, $message);
                                    $message = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) { return strtolower($matches[0]); }, $message);
                                }
                            }
                            else
                            {
                                $message = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $msg))));
                                $message = preg_replace_callback('/[.!?].*?\w/', function ($matches) {
                                    return strtoupper($matches[0]);
                                }, $message);
                                $message = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) {
                                    return strtolower($matches[0]);
                                }, $message);
                            }
                        }
                    }
                    else
                    {
                        $message = ucfirst(str_replace(' id ', ' ID ', strtolower(preg_replace("/(\.|,)$/", "", $message))));
                        $message = preg_replace_callback('/[.!?].*?\w/', function ($matches) {
                            return strtoupper($matches[0]);
                        }, $message);
                        $message = preg_replace_callback('/[[A-Za-z0-9.]+@[A-Za-z0-9.]+/', function ($matches) {
                            return strtolower($matches[0]);
                        }, $message);
                    }
                }
            }
            $response['status_code'] = $code;
            $response['success']     = $success;
            $response['message']     = $message;
            if ($pagination)
            {
                $response['pages'] = $pagination;
            }
            $response['data'] = $data;

            return $response;
        }
    }
