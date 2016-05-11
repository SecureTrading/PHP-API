<?php

namespace Securetrading\Stpp\JsonInterface\Benchmarks;

require(realpath(__DIR__ . '/../vendor/autoload.php'));
require(realpath(__DIR__ . '/../tests/Helper.php'));

use \Securetrading\Stpp\JsonInterface\Tests\Integration\ApiTest;

class ApiBench implements \PhpBench\Benchmark {
  private function _processOneAuth() {
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
      'requesttypedescription' => 'AUTH',
      'pan' => '4111110000000211',
      'expirymonth' => '11',
      'expiryyear' => '2031',
      'securitycode' => '123',
      'paymenttypedescription' => 'VISA',
      'mainamount' => '10.00',
      'currencyiso3a' => 'GBP',
      'sitereference' => $testConfig['siteReference'],
      'accounttypedescription' => 'ECOM',
    );

    $api = $ioc->get('\Securetrading\Stpp\JsonInterface\Api', $config);
    $response = $api->process($request);
    $responseData = $response->toArray();
    
    if ($responseData['responses'][0]['errorcode'] !== '0') {
      throw new \Exception('Bad errorcode: ' . $actualResponseData['responses'][0]['errorcode']);
    } 
  }

  /**
   * @iterations 6
   */
  public function benchQuickApi() {
    $this->_processOneAuth();
  }

  /**
   * @iterations 1
   * @paramProvider params_benchFullApi
   */
  public function benchFullApi($iteration) {
    $maxLengthOfTestInSeconds = (int) $iteration->getParameter('max_length_of_test_in_seconds');
    $transactionsToProcess = (int) $iteration->getParameter('transactions_to_process');

    $processed = false;
    $startTime = time();
    $i = 0;

    while((time() - $startTime) < $maxLengthOfTestInSeconds) {
      $this->_processOneAuth();
      if ($i++ === $transactionsToProcess) {
	$processed = true;
	break;
      }
    }

    if (!$processed) {
      throw new \Exception(sprintf('Ran out of time (%s seconds) before %s transactions could be processed.  %s were processed.', $maxLengthOfTestInSeconds, $transactionsToProcess, $i));
    }
  }

  public function params_benchFullApi() {
    return array(
      array(
	'max_length_of_test_in_seconds' => '60',
	'transactions_to_process' => '500',
      ),
    );
  }
}