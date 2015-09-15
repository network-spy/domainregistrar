<?php

namespace DomainRegistrar;

/**
 * Class Config
 * @package DomainRegistrar
 */
class Config
{

    const DS = DIRECTORY_SEPARATOR;

    /**
     * @var array
     */
    public static $sysParams = [
        // Секретное слово для защиты
        'SECRET_WORD' => '\/3RY_53CR37_\/\/0R|)',
        // Настройки API сервисов
        // Параметры, омеченные (*) - обязательны для заполнения
        'DOMAIN_SERVICES' => [
            'GoDaddy' => [
                'DOMAIN_ZONE' => 'com', // (*) Доменная зона для сервиса
                'X_SHOPPER_ID' => '', // (*) Client# который выдается при регистрации на сервисе
                'TEST_MODE' => true, // Тестовый режим true- вкл, false-откл
                 /* Тестовые ключи: */
                'API_KEY' => '',
                'API_SECRET' => '',
                'API_URL' => 'https://api.ote-godaddy.com', // URL для тестирования
                /* Production ключи*/
                //'API_KEY' => '', // (*)
                //'API_SECRET' => '', // (*)
                //'API_URL' => 'https://api.godaddy.com', // (*) URL для радобы с API
                'ENDUSER_IP' => '92.62.96.50', // (*) Ваш IP
                'FIRST_NAME' => 'Viacheslav', // (*) Фамилия
                'MIDDLE_NAME' => '',
                'LAST_NAME' => 'Tereshchenko', // (*) Имя
                'ORGANIZATION' => 'Test Ltd', // (*) Название организации
                'JOB_TITLE' => '',
                'EMAIL' => 'test@test.com', // (*) Электоронная почта
                'PHONE' => '+380.932213202', // (*) Номер телефона (формат: +код_страны.xxxxxxxxx)
                'FAX' => '',
                'MAILING_ADDRESS1' => '123 Lenina street', // (*) Адрес
                'MAILING_ADDRESS2' => '',
                'MAILING_ADDRESS_CITY' => 'Odessa', // (*) Город
                'MAILING_ADDRESS_STATE' => 'Ukraine', // (*) Страна
                'MAILING_ADDRESS_POSTAL_CODE' => '65005', // (*) Почтовый индекс
            ],
            'TwoDomains' => [ //2domains
                'DOMAIN_ZONE' => 'ru', // (*) Доменная зона для сервиса
                'API_URL' => 'https://2domains.ru/reg/api2', // (*) URL для радобы с API
                'USER_NAME' => 'login', // (*) Логин
                'PASSWORD' => 'pass', // (*) Пароль
                'FOLDER_NAME' => 'test@test.ru', // (*) Имя папки, к которой должен быть привязан домен. Папку в аккаунте нужно создать заранее до регистрации.
                'SUB_USER_FOLDER_NAME' => 'test@test.ru', // (*) Имя папки тоже
                'ENDUSER_IP' => '92.62.96.50', // (*) Ваш IP
                'POST_ADDRESS' => "198332\nМосковская облась\nМосква\nУлица Ленина, д.1, кв.15\nИванову Ивану Ивановичу", // (*) Почтовый адрес
                'JURISTIC_ADDRESS' => '634049 Томская обл. г. Томск, ул. Ивана Черных 20', // (*) Юридический адрес
                'PHONE' => '+74951112233', // (*) Телефон
                'SMS_SECURITY_NUMBER' => '+76786565768', // (*) Телефон для отправки смс в целях безопасности
                'EMAIL' => 'email@mail.ru', // (*) Электоронная почта
                'FAX' => '',
                'ORGANIZATION' => 'OrgName Ltd', // (*) Название организации
                'ORGANIZATION_RUS' => 'ООО "Ромашка\"', // (*) Название организации на русском языке
                'INN_CODE' => '7017100180', // (*) ИНН код
                'KPP_CODE' => '701701001' // (*) КПП код
            ]
        ],
        'STORAGE_FILE_NAME' => 'Tasks.csv', // (*) Файл, где будт храниться данные о зарегестрированных доменах
        'LIMIT_OF_RECORDS_PROCESSING' => 10, // (*) Количество обрабатываемых за раз доменов для привязки IP
        'LOG_FILE_NAME' => 'errors.log'
    ];

    /**
     * @return string
     */
    public static function getRootPath() {
        return realpath(dirname(__FILE__)) . self::DS;
    }

    /**
     * @return string
     */
    public static function getServerRootPath() {
        return self::getRootPath() . '..' . self::DS . '..' . self::DS . '..' . self::DS;
    }

    /**
     * @return string
     */
    public static function getStorageFilePath() {
        return self::getRootPath() . self::$sysParams['STORAGE_FILE_NAME'];
    }

    /**
     * @return string
     */
    public static function getLogFilePath() {
        return self::getRootPath() . self::$sysParams['LOG_FILE_NAME'];
    }

}