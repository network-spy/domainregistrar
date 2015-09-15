<?php

namespace DomainRegistrar;

/**
 * Interface StorageInterface
 * @package DomainRegistrar
 */
interface StorageInterface {

    /**
     * @param RegistrationData $data
     * @return mixed
     */
    public function add(RegistrationData $data);

    /**
     * @param $limit
     * @return mixed
     */
    public function getNew($limit);

    /**
     * @param array $registrationDataList
     * @return mixed
     */
    public function saveList(array $registrationDataList);

}