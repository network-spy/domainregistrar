<?php

namespace DomainRegistrar;

/**
 * Class CSVStorage
 * @package DomainRegistrar
 */
class CSVStorage implements StorageInterface {

    /*
     * Names and positions of fields in CSV file
     */
    const ORDER_ID = 0;
    const SERVICE_NAME = 1;
    const DOMAIN_NAME = 2;
    const IP = 3;
    const STATUS = 4;
    const TIME = 5;
    const RESPONSE_DATA = 6;

    /**
     * @var string
     */
    private $filePath = '';

    /**
     * @var null|resource
     */
    private $hFile = null;

    /**
     * @param $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->hFile = fopen($filePath, 'ab+');
        if (!$this->hFile) {
            throw new \Exception("Невозможно открыть/создать файл для чтения/записи информации");
        }
    }

    /**
     * @param RegistrationData $data
     * @return bool
     */
    public function add(RegistrationData $data)
    {
        if (empty($data->orderId)) {
            return false;
        }
        $record[self::ORDER_ID] = $data->orderId;
        $record[self::SERVICE_NAME] = $data->serviceName;
        $record[self::DOMAIN_NAME] = $data->domainName;
        $record[self::IP] = $data->IP;
        $record[self::STATUS] = $data->status;
        $record[self::TIME] = $data->time;
        $record[self::RESPONSE_DATA] = $data->responseData;
        fputcsv($this->hFile, $record, ';', '"');
        return true;
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getNew($limit = 0)
    {
        $records = [];
        rewind($this->hFile);
        while (($record = fgetcsv($this->hFile, 1000, ';')) !== FALSE) {
            if ($record[self::STATUS] !== 'NEW') {
                continue;
            } elseif ($limit > 0 && count($records) >= $limit) {
                break;
            }
            $regData = new RegistrationData();
            $regData->orderId = $record[self::ORDER_ID];
            $regData->serviceName = $record[self::SERVICE_NAME];
            $regData->domainName = $record[self::DOMAIN_NAME];
            $regData->IP = $record[self::IP];
            $regData->status = $record[self::STATUS];
            $regData->time = $record[self::TIME];
            $regData->responseData = $record[self::RESPONSE_DATA];
            $records[] = $regData;
        }
        return $records;
    }

    /**
     * @param array $registrationDataList
     * @throws \Exception
     */
    public function saveList(array $registrationDataList)
    {
        if (empty($registrationDataList)) {
            return;
        }
        $hTempFile = fopen($this->filePath.'.tmp', 'wb');
        if (!$hTempFile) {
            throw new \Exception("Невозможно создать временный файл для сохранения информации");
        }
        rewind($this->hFile);
        while (($record = fgetcsv($this->hFile, 2048, ';')) !== FALSE) {
            foreach($registrationDataList as $regData) {
                if ($record[self::ORDER_ID] != $regData->orderId ||
                    $record[self::SERVICE_NAME] != $regData->serviceName) {
                    continue;
                }
                $record[self::ORDER_ID] = $regData->orderId;
                $record[self::SERVICE_NAME] = $regData->serviceName;
                $record[self::DOMAIN_NAME] = $regData->domainName;
                $record[self::IP] = $regData->IP;
                $record[self::STATUS] = $regData->status;
                $record[self::TIME] = $regData->time;
                $record[self::RESPONSE_DATA] = $regData->responseData;
            }
            fputcsv($hTempFile, $record, ';', '"');
        }
        fclose($hTempFile);
        fclose($this->hFile);
        if (!rename($this->filePath, $this->filePath.'.old')) {
            throw new \Exception("Невозможно переименовать файл для изменения информации");
        }
        if (!rename($this->filePath.'.tmp', $this->filePath)) {
            throw new \Exception("Невозможно переименовать файл c новой информации");
        }
        if (!unlink($this->filePath.'.old')) {
            throw new \Exception("Невозможно удалить файл cо старой информацией");
        }
        $this->hFile = fopen($this->filePath, 'ab+');
        if (!$this->hFile) {
            throw new \Exception("Невозможно открыть/создать файл для чтения/записи информации");
        }
    }

}