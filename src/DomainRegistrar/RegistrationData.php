<?php

namespace DomainRegistrar;

/**
 * Class RegistrationData
 * @package DomainRegistrar
 */
class RegistrationData {

    /**
     * @var string
     */
    public $domainName;

    /**
     * @var string
     */
    public $IP;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $orderId;

    /**
     * @var string
     */
    public $serviceName;

    /**
     * @var string
     */
    public $time;

    /**
     * @var string
     */
    public $responseData;

    /**
     * @param array $params
     */
    public function __construct(array $params=[])
    {
        $this->orderId = empty($params['orderId']) ? '' : $params['orderId'];
        $this->serviceName = empty($params['service']) ? '' : $params['service'];
        $this->responseData = empty($params['data']) ? '' : $params['data'];
        $this->domainName = empty($params['domain']) ? '' : $params['domain'];
        $this->IP = empty($params['ip']) ? '' : $params['ip'];
        $this->status = empty($params['status']) ? 'NEW' : empty($params['status']);
        $this->time = empty($params['time']) ? date('Y-m-d H:i:s') : $params['time'];
    }

} 