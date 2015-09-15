<?php

namespace DomainRegistrar;

/**
 * Class Application
 * @package DomainRegistrar
 */
class Application {

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param $ip
     * @param $domain
     * @param $hash
     * @return bool
     */
    public function registerDomain($ip, $domain, $hash)
    {
        $ip = trim($ip);
        $domain = trim(strtolower($domain));
        $hash = trim($hash);
        if (empty($ip) || empty($domain) || empty($hash)) {
            $this->errors[] = 'Все параметры(IP, домен, хэш) должны быть указаны';
        } elseif (md5($ip.$domain.Config::$sysParams['SECRET_WORD']) != $hash) {
            $this->errors[] = 'Неверный хэш';
        } elseif (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $this->errors[] = 'Ошибка! Неверный IP';
        } elseif (preg_match('^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$', $domain)) {
            $this->errors[] = 'Ошибка! Неверный домен';
        }
        if (!empty($this->errors)) {
            return false;
        }
        $domainZone = substr($domain, strrpos($domain, '.') + 1);
        $service = null;
        try {
            $HTTPClient = new CURLClient();
            switch ($domainZone) {
                case 'ru':
                    $service = DomainServiceFactory::create('TwoDomains', $HTTPClient); //2domains
                    break;
                case 'com':
                    $service = DomainServiceFactory::create('GoDaddy', $HTTPClient);
                    break;
                default:
                    throw new \Exception("Доменная зона не доступна!");

            }
            $result = $service->register($domain);
            $result['domain'] = $domain;
            $result['ip'] = $ip;
            $registrationData = new RegistrationData($result);
            $storage = new CSVStorage(Config::getStorageFilePath());
            $storage->add($registrationData);
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        if (!empty($this->errors)) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function changeIPs()
    {
        try {
            $HTTPClient = new CURLClient();
            $storage = new CSVStorage(Config::getStorageFilePath());
            $newRecords = $storage->getNew(Config::$sysParams['LIMIT_OF_RECORDS_PROCESSING']);
            $recordsCount = count($newRecords);
            $services = [];
            for ($i = 0; $i < $recordsCount; $i++) {
                if (!isset($services[$newRecords[$i]->serviceName])) {
                    $services[$newRecords[$i]->serviceName] = DomainServiceFactory::create($newRecords[$i]->serviceName, $HTTPClient);
                }
                $services[$newRecords[$i]->serviceName]->processIP($newRecords[$i]);
                $newRecords[$i]->status = 'DONE';
            }
            $storage->saveList($newRecords);
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            $this->logMsg($e->getMessage());
        }
        if (!empty($this->errors)) {
            return false;
        }
        return true;
    }

    /**
     * @param $message
     */
    private function logMsg($message)
    {
        $data = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
        file_put_contents(Config::getLogFilePath(), $data, FILE_APPEND);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}