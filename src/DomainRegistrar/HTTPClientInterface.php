<?php

namespace DomainRegistrar;

/**
 * Interface HTTPClientInterface
 * @package DomainRegistrar
 */
interface HTTPClientInterface {

    /**
     * @param $method
     * @param $url
     * @param array $headers
     * @param string $data
     * @return mixed
     */
    public function sendRequest($method, $url, array $headers=[], $data='');

    /**
     * @param $base_url
     * @return mixed
     */
    public function setBaseURL($base_url);

    /**
     * @return mixed
     */
    public function getResponseCode();

}
