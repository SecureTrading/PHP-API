<?php

if (!($autoload = realpath(__DIR__ . '/../../../autoload.php')) && !($autoload = realpath(__DIR__ . '/../vendor/autoload.php'))) {
  throw new \Exception('Composer autoloader file could not be found.');
}

require_once($autoload);

$configData = array(
  'username' => 'your_web_services_username@test.com',
  'password' => 'your_password',
);

$requestData = array(
  'sitereference' => 'your_site_reference',
  'requesttypedescription' => 'AUTH',
  'accounttypedescription' => 'ECOM',
  'currencyiso3a' => 'GBP',
  'mainamount' => '100',
  'pan' => '4111110000000211',
  'expirymonth' => '10',
  'expiryyear' => '2022',
  'securitycode' => '123',
);

$api = \Securetrading\api($configData);
$response = $api->process($requestData);

var_dump($response);