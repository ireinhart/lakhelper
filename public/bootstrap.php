<?php

$libPath = dirname(__FILE__) . '/../library/';
set_include_path($libPath . ':' . get_include_path());

include($libPath . 'Zend/Loader/StandardAutoloader.php');

// Setup autoloading
$loader = new Zend\Loader\StandardAutoloader();
$loader->setFallbackAutoloader(true);
$loader->register();

require(__DIR__ . '/config.php');

// database
$dbAdapter = new Zend\Db\Adapter\Adapter(array(
    'driver'   => $system['db']['driver'],
    'host'     => $system['db']['host'],
    'database' => $system['db']['name'],
    'username' => $system['db']['username'],
    'password' => $system['db']['password'],
));

$sql = new Zend\Db\Sql\Sql($dbAdapter);

$httpClient = new Zend\Http\Client;

$httpClient->setOptions(array(
    'timeout'      => 60
));

$headers = new Zend\Http\Headers();
$headers->addHeaderLine('XYClient-Capabilities', 'base,fortress,partialUpdate,simplePlayerReport');
$headers->addHeaderLine('User-Agent', 'BKClient/4.3.0 (iPhone OS 8.1.2 / iPhone5,2)');

$httpClient->setHeaders($headers);
