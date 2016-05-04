<?php

namespace Securetrading\Stpp\JsonInterface\Benchmarks;

require(realpath(__DIR__ . '/../../../autoload.php'));
require(realpath(__DIR__ . '/../tests/Helper.php'));

use \Securetrading\Stpp\JsonInterface\Tests\Integration\ApiTest;

class ApiBench implements \PhpBench\Benchmark {
  /**
   * @iterations 6
   */
  public function benchApi() {
    $helper = new \Securetrading\Unittest\Helper();

    $benchDir = $helper->getTestDir('benchmarks');

    $ioc = \Securetrading\Stpp\JsonInterface\Main::bootstrapIoc();
    
    $ioc->setOption('stpp_json_log_filename', 'json_benchmark_log');
    $ioc->setOption('stpp_json_log_filepath', $benchDir . 'logs' . DIRECTORY_SEPARATOR);
    $ioc->setOption('stpp_json_log_archive_filepath', $benchDir . 'logs' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR);

    $filePath = realpath(__DIR__ . '/../tests/config.ini');
    $testConfig = $helper->parseIniFile($filePath, array('username', 'password', 'siteReference'));

    $config = array(
      'connections' => array(
	'json_interface' => array(
	  'username' => $testConfig['username'],
	  'password' => $testConfig['password'],
	  'ssl_verify_peer' => false,
	  'ssl_verify_host' => 0,
	),
      ),
    );

    $request = array(
      'requesttypedescription' => 'CACHETOKENISE',
      'pan' => '4111110000000211',
      'expirymonth' => '11',
      'expiryyear' => '2031',
      'securitycode' => '123',
      'paymenttypedescription' => 'VISA',
      'sitereference' => $testConfig['siteReference'],
    );

    $api = $ioc->get('\Securetrading\Stpp\JsonInterface\Api', $config);
    $response = $api->process($request);
    $responseData = $response->toArray();
    
    if ($responseData['responses'][0]['errorcode'] !== '0') {
      throw new \Exception('Bad errorcode: ' . $actualResponseData['responses'][0]['errorcode']);
    } 
  }
}