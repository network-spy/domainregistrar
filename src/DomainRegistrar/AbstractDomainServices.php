<?php

namespace DomainRegistrar;

/**
 * Class AbstractDomainServices
 * @package DomainRegistrar
 */
abstract class AbstractDomainServices {

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var HTTPClientInterface|null
     */
    protected $HTTPClient = null;

    /**
     * @param array $params
     * @param HTTPClientInterface $HTTPClient
     */
    public function __construct(array $params, HTTPClientInterface $HTTPClient)
    {
        $this->params = $params;
        $this->HTTPClient = $HTTPClient;
    }

    /**
     * @param $domain
     * @return mixed
     */
    abstract public function register($domain);

    /**
     * @param RegistrationData $registrationData
     * @return mixed
     */
    abstract public function processIP(RegistrationData $registrationData);

}