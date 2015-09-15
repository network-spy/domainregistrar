<?php

namespace DomainRegistrar\DomainServices;

use DomainRegistrar\AbstractDomainServices;
use DomainRegistrar\RegistrationData;

/**
 * Class GoDaddy
 * @package DomainRegistrar\DomainServices
 */
class GoDaddy extends AbstractDomainServices {

    /**
     * @param $domain
     * @return array
     * @throws \Exception
     */
    public function register($domain)
    {
        $this->HTTPClient->setBaseURL($this->params['API_URL']);
        if (!$this->params['TEST_MODE']) {
            $headers = [
                "Authorization: sso-key {$this->params['API_KEY']}:{$this->params['API_SECRET']}",
                "X-Shopper-Id: {$this->params['X_SHOPPER_ID']}",
            ];
        }
        $headers[] = 'Accept: application/json';
        // Check if available
        $response = $this->HTTPClient->sendRequest(
            'GET',
            "/v1/domains/available?domain={$domain}&checkType=FAST&forTransfer=false",
            $headers
        );
        $response = json_decode($response);
        $this->errorsProcess($response);
        if ($response->available === false) {
            throw new \Exception("Домен не доступен");
        }
        // Get schema
        $response = $this->HTTPClient->sendRequest(
            'GET',
            "/v1/domains/purchase/schema/{$this->params['DOMAIN_ZONE']}",
            $headers
        );
        $schema = json_decode($response);
        $this->errorsProcess($response);
        // Get agreement
        $response = $this->HTTPClient->sendRequest(
            'GET',
            "/v1/domains/agreements?tlds={$this->params['DOMAIN_ZONE']}&privacy=true",
            $headers
        );
        $agreements = json_decode($response);
        $this->errorsProcess($response);
        $contact = [
            'nameFirst' => $this->params['FIRST_NAME'],
            'nameMiddle' => $this->params['MIDDLE_NAME'],
            'nameLast' => $this->params['LAST_NAME'],
            'organization' => $this->params['ORGANIZATION'],
            'jobTitle' => $this->params['JOB_TITLE'],
            'email' => $this->params['EMAIL'],
            'phone' => $this->params['PHONE'],
            'fax' => $this->params['FAX'],
            'addressMailing' => [
                'address1' => $this->params['MAILING_ADDRESS1'],
                'address2' => $this->params['MAILING_ADDRESS2'],
                'city' => $this->params['MAILING_ADDRESS_CITY'],
                'state' => $this->params['MAILING_ADDRESS_STATE'],
                'postalCode' => $this->params['MAILING_ADDRESS_POSTAL_CODE'],
                'country' => 'RU'
            ]
        ];
        $domainProperties = clone $schema->properties;
        $domainProperties->domain = $domain;
        $domainProperties->nameServers = [];
        $domainProperties->period = 1;
        $domainProperties->renewAuto = true;
        $domainProperties->privacy = true;
        $domainProperties->contactRegistrant = $contact;
        $domainProperties->contactAdmin = $contact;
        $domainProperties->contactTech = $contact;
        $domainProperties->contactBilling = $contact;
        $domainProperties->consent = [
            'agreedBy' => $this->params['ENDUSER_IP'],
            'agreedAt' => date('Y-m-d').'T'.date('H:i:s').'Z'
        ];
        foreach ($agreements as $agreement) {
            $domainProperties->consent['agreementKeys'][] = $agreement->agreementKey;
        }
        // Fields validation according to formats in schema
        $this->validate($domainProperties, $schema);
        // Buy domain
        $headers[] = 'Content-Type: application/json';
        $response = $this->HTTPClient->sendRequest(
            'POST',
            '/v1/domains/purchase',
            $headers,
            json_encode($domainProperties)
        );
        $response = json_decode($response);;
        $this->errorsProcess($response);

        //return result
        return [
            'orderId' => $response->orderId,
            'service' => 'GoDaddy',
            'data' => json_encode($response)
        ];
    }

    /**
     * @param RegistrationData $registrationData
     * @return mixed|void
     * @throws \Exception
     */
    public function processIP(RegistrationData $registrationData)
    {
        $this->HTTPClient->setBaseURL($this->params['API_URL']);
        if (!$this->params['TEST_MODE']) {
            $headers = [
                "Authorization: sso-key {$this->params['API_KEY']}:{$this->params['API_SECRET']}",
                "X-Shopper-Id: {$this->params['X_SHOPPER_ID']}",
            ];
        }
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/json';
        $request = [
            ['name' => '@', 'data' => $registrationData->IP],
            ['name' => '*', 'data' => $registrationData->IP]
        ];
        $response = $this->HTTPClient->sendRequest(
            'PUT',
            "/v1/domains/{$registrationData->domainName}/records/A",
            $headers,
            json_encode($request)
        );
        $response = json_decode($response);
        $this->errorsProcess($response);
        if ($this->HTTPClient->getResponseCode() !== 200) {
            throw new \Exception("Ошибка! Код: {$this->HTTPClient->getResponseCode()}");
        }
    }

    /**
     * @param $domainProperties
     * @param $schema
     * @throws \Exception
     */
    private function validate($domainProperties, $schema)
    {
        foreach ($schema->required as $field) {
            if (empty($domainProperties->$field)) {
                throw new \Exception("Не указано поле {$field}");
            } elseif (!empty($schema->properties->$field->pattern) &&
                $field != 'domain' && // because weird expression
                !preg_match($schema->properties->$field->pattern, $domainProperties->$field
                )
            ) {
                throw new \Exception("Неверный формат поля {$field}");
            }
        }
        foreach ($schema->definitions->Contact->required as $field) {
            if (empty($domainProperties->contactRegistrant[$field])) {
                throw new \Exception("Не указано поле {$field}");
            } elseif (!empty($schema->properties->$field->maxLength) &&
                strlen($domainProperties->contactRegistrant[$field]) > $schema->properties->$field->maxLength
            ) {
                throw new \Exception("Превышена максимальная длина поля {$field}");
            }
        }
        foreach ($schema->definitions->Address->required as $field) {
            if (empty($domainProperties->contactRegistrant['addressMailing'][$field])) {
                throw new \Exception("Не указано поле {$field}");
            } elseif (!empty($schema->properties->$field->maxLength) &&
                strlen($domainProperties->contactRegistrant['addressMailing'][$field]) > $schema->properties->$field->maxLength
            ) {
                throw new \Exception("Превышена максимальная длина поля {$field}");
            } elseif (!empty($schema->properties->$field->minLength) &&
                strlen($domainProperties->contactRegistrant['addressMailing'][$field]) > $schema->properties->$field->minLength
            ) {
                throw new \Exception("Не указан необходимый минимум длины поля {$field}");
            }
        }
    }

    /**
     * @param $response
     * @throws \Exception
     */
    private function errorsProcess($response)
    {
        if (!empty($response->code)) {
            throw new \Exception("Ошибка! Код: {$response->code} Сообщение: {$response->message}");
        }
    }

}