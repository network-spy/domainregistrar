<?php

include_once '../src/loader.php';

$ip = '123.123.123.123';
$domain = 'domain.com';
$hash = md5($ip.$domain.\DomainRegistrar\Config::$sysParams['SECRET_WORD']);

$app = new DomainRegistrar\Application();
if (!$app->registerDomain($ip, $domain, $hash) ) {
    foreach ($app->getErrors() as $error) {
        echo $error;
    }
}