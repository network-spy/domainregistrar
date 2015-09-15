<?php

namespace DomainRegistrar\DomainServices;

use DomainRegistrar\AbstractDomainServices;
use Phois\Whois\Whois;
use DomainRegistrar\RegistrationData;

/**
 * Class TwoDomains
 * @package DomainRegistrar\DomainServices
 */
class TwoDomains extends AbstractDomainServices {

    /**
     * @param $domain
     * @return array
     * @throws \Exception
     */
    public function register($domain)
    {
        if (!(new Whois($domain))->isAvailable()) {
            throw new \Exception("Домен не доступен");
        }
        $serviceID = uniqid();
        $contacts = [];
        $contacts['country'] = 'RU';
        $contacts['rp_profile_type'] = 'y'; //юр лицо
        $contacts['p_addr'] = $this->params['POST_ADDRESS'];
        $contacts['address_r'] = $this->params['JURISTIC_ADDRESS'];
        $contacts['phone'] = $this->params['PHONE'];
        $contacts['sms_security_number'] = $this->params['SMS_SECURITY_NUMBER'];
        $contacts['e_mail'] = $this->params['EMAIL'];
        $contacts['fax'] = $this->params['FAX'];
        $contacts['org'] = $this->params['ORGANIZATION'];
        $contacts['org_r'] = $this->params['ORGANIZATION_RUS'];
        $contacts['code'] = $this->params['INN_CODE'];
        $contacts['kpp'] = $this->params['KPP_CODE'];
        $domainProperties = [];
        $domainProperties['action'] = 'domain/create';
        $domainProperties['output_format'] = 'json';
        $domainProperties['input_format'] = 'json';
        $domainProperties['period'] = '1';
        $domainProperties['__trusted'] = '1';
        $domainProperties['ok_if_no_money'] = '1';
        $domainProperties['username'] = $this->params['USER_NAME'];
        $domainProperties['password'] = $this->params['PASSWORD'];
        $domainProperties['folder_name'] = $this->params['FOLDER_NAME'];
        $domainProperties['sub_user_folder_name'] = $this->params['SUB_USER_FOLDER_NAME'];
        $domainProperties['enduser_ip'] = $this->params['ENDUSER_IP'];
        $domainProperties['input_data'] = json_encode(
            [
                'contacts' => $contacts,
                'domains' => [['dname' => $domain, 'user_servid' => $serviceID]],
                'nss' => ['ns0' => 'ns1.reg.ru', 'ns1' => 'ns2.reg.ru']
            ],
            JSON_UNESCAPED_UNICODE
        );
        $this->validate($domainProperties, $contacts);
        $this->HTTPClient->setBaseURL($this->params['API_URL']);
        // buy domain
        $response = $this->HTTPClient->sendRequest('POST', $this->params['API_URL'], [], json_encode($domainProperties));
        $response = json_decode($response);
        $this->errorsProcess($response);
        //return results
        return [
            'orderId' => $serviceID,
            'service' => 'TwoDomains',
            'data' => json_encode($response)
        ];
    }

    /**
     * @param RegistrationData $registrationData
     * @return void
     */
    public function processIP(RegistrationData $registrationData)
    {
        $request = [];
        $request['action'] = 'zone/update_records';
        $request['input_format'] = 'json';
        $request['sub_user_folder_name'] = $this->params['FOLDER_NAME'];
        $request['username'] = $this->params['USER_NAME'];
        $request['password'] = $this->params['PASSWORD'];
        $request['__trusted'] = 1;
        $request['ok_if_no_money'] = 1;
        $request['input_data'] = json_encode(
            [
                'services' => [['service_id' => $registrationData->orderId]],
                'action_list' => [
                    ['action' => 'add_alias', 'subdomain' => 'www', 'ipaddr' => $registrationData->IP],
                    ['action' => 'add_alias', 'subdomain' => '@', 'ipaddr' => $registrationData->IP],
                    ['action' => 'add_alias', 'subdomain' => '*', 'ipaddr' => $registrationData->IP],
                ]
            ]
        );
        $response = $this->HTTPClient->sendRequest('POST', $this->params['API_URL'], [], json_encode($request));
        $response = json_decode($response);
        $this->errorsProcess($response);
    }

    /**
     * @param array $domainProperties
     * @param array $contacts
     * @throws \Exception
     */
    private function validate(array $domainProperties, array $contacts)
    {
        if (empty($domainProperties['username'])) {
            throw new \Exception("Не указано имя пользователя");
        } elseif (empty($domainProperties['password'])) {
            throw new \Exception("Не указан пароль");
        } elseif (empty($domainProperties['folder_name'])) {
            throw new \Exception("Не указано имя папки");
        } elseif (empty($domainProperties['sub_user_folder_name'])) {
            throw new \Exception("Не указано имя папки");
        } elseif (empty($domainProperties['enduser_ip'])) {
            throw new \Exception("Не указан IP");
        } elseif (empty($contacts['p_addr'])) {
            throw new \Exception("Не указан почтовый адрес");
        } elseif (empty($contacts['address_r'])) {
            throw new \Exception("Не указан юридический");
        } elseif (empty($contacts['phone'])) {
            throw new \Exception("Не указан телефон");
        } elseif (empty($contacts['sms_security_number'])) {
            throw new \Exception("Не указан телефон для смс");
        } elseif (empty($contacts['org'])) {
            throw new \Exception("Не указано название организации");
        } elseif (empty($contacts['org_r'])) {
            throw new \Exception("Не указано название организации на английском");
        } elseif (empty($contacts['code'])) {
            throw new \Exception("Не указан ИНН");
        } elseif (empty($contacts['kpp'])) {
            throw new \Exception("Не указан КПП");
        }
    }

    /**
     * @param $response
     * @throws \Exception
     */
    private function errorsProcess($response)
    {
        if (!empty($response->error_text)) {
            throw new \Exception("Ошибка! Сообщение: {$response->error_text}");
        }
    }


    /**
     * @param $domain
     * @return bool
     * @throws \Exception

    public function whois($domain)
    {
        $servers = [
            //"ru" => "whois.ripn.net",
            'ru' => 'whois.nic.ru',
        ];
        $connection = fsockopen($servers[$this->params['DOMAIN_ZONE']], 43);
        if (!$connection) {
            throw new \Exception("Can not connect to WHOIS server!");
        }
        fwrite($connection, $domain."\r\n");
        $response = '';
        while (!feof($connection)) {
            $response .= fread($connection, 256);
        }
        fclose($connection);
        if (preg_match('/state/', $response)) {
            return true;
        }
        return false;
    }
     */

}