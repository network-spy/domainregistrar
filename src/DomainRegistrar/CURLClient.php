<?php

namespace DomainRegistrar;

/**
 * Class CURLClient
 * @package DomainRegistrar
 */
class CURLClient implements HTTPClientInterface {

    /**
     * @var
     */
    private $base_url;

    /**
     * @var resource
     */
    private $curl;

    public function __construct()
    {
        $this->curl = curl_init();
        if(!$this->curl) {
            throw new \Exception("cURL is not installed!");
        }
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false
            ]
        );
    }

    /**
     * @param $base_url
     * @return $this
     */
    public function setBaseURL($base_url)
    {
        $this->base_url = $base_url;
        return $this;
    }

    /**
     * @param $method
     * @param $url
     * @param array $headers
     * @param string $data
     * @return mixed
     * @throws \Exception
     */
    public function sendRequest($method, $url, array $headers=[], $data='')
    {
        $method = strtoupper($method);
        $url = $this->buildURL($url);
        switch ($method) {
            case 'GET':
                $this->get($url, $headers);
                break;
            case 'POST':
                $this->post($url, $headers, $data);
                break;
            case 'PUT':
                $this->put($url, $headers, $data);
                break;
            case 'DELETE':
                $this->delete($url, $headers);
                break;
            default:
                throw new \Exception("Wrong HTTP method");
        }
        return curl_exec($this->curl);
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        $info = curl_getinfo($this->curl);
        return (int)$info['http_code'];
    }

    /**
     * @return mixed
     */
    public function getInfoAboutLastRequest()
    {
        return curl_getinfo($this->curl);
    }

    /**
     * @param $url
     * @param $headers
     */
    protected function get($url, $headers)
    {
        if (!empty($headers)) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_URL => $url,
                CURLOPT_FRESH_CONNECT => true
            ]
        );
    }

    /**
     * @param $url
     * @param $headers
     * @param $data
     */
    protected function post($url, $headers, $data)
    {
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $data
            ]
        );
        if (!empty($headers)) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }
    }

    /**
     * @param $url
     * @param $headers
     * @param $data
     */
    protected function put($url, $headers, $data)
    {
        curl_setopt_array(
            $this->curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_POSTFIELDS => $data
            ]
        );
        if (!empty($headers)) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }
    }

    /**
     * @param $url
     * @param $headers
     */
    protected function delete($url, $headers)
    {
        //TODO: implement method with option CURLOPT_CUSTOMREQUEST => "DELETE"
    }

    /**
     * @param $url
     * @return string
     * @throws \Exception
     */
    private function buildURL($url)
    {
        if (empty($this->base_url)) {
            throw new \Exception("Base URL is not set");
        } elseif (empty($url)) {
            return $this->base_url;
        } elseif (strpos($url, 'http') === 0) {
            return $url;
        } elseif (substr($this->base_url, -1) === '/' && substr($url, 0, 1) === '/') {
            return $this->base_url . substr($url, 1);
        } elseif (substr($this->base_url, -1) !== '/' && substr($url, 0, 1) !== '/') {
            return $this->base_url . '/' . $url;
        } else {
            return $this->base_url . $url;
        }
    }

}