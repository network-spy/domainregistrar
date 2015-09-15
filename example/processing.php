<?php

include_once '../src/loader.php';

$app = new DomainRegistrar\Application();

if (!$app->changeIPs()) {
    foreach ($app->getErrors() as $error) {
        echo $error;
    }
}