<?php

namespace DomainRegistrar;

/**
 * Class DomainServiceFactory
 * @package DomainRegistrar
 */
class DomainServiceFactory
{

    private function __construct() {}

    private function __clone() {}

    /**
     * @param $domainServiceName
     * @param HTTPClientInterface $HTTPClient
     * @return mixed
     * @throws \Exception
     */
    public static function create($domainServiceName, HTTPClientInterface $HTTPClient)
    {
        if (empty($domainServiceName) ||
            !in_array($domainServiceName, array_keys(Config::$sysParams['DOMAIN_SERVICES']))
        ) {
            throw new \Exception("Wrong service name: {$domainServiceName}!");
        }
        $domainServiceClassName = self::getDomainServiceClassName($domainServiceName);
        if (!$domainServiceClassName) {
            throw new \Exception("Class for service {$domainServiceName} not found!");
        }
        return new $domainServiceClassName(Config::$sysParams['DOMAIN_SERVICES'][$domainServiceName], $HTTPClient);
    }

    /**
     * @param $domainServiceName
     * @return null|string
     */
    private static function getDomainServiceClassName($domainServiceName)
    {
        $domainServiceClass = 'DomainRegistrar\\' . sprintf('DomainServices\%s', $domainServiceName);
        if (class_exists($domainServiceClass, true)) {
            return $domainServiceClass;
        }
        return null;
    }

}