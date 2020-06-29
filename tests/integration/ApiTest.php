<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Integration;

class ApiTest extends \Securetrading\Unittest\IntegrationtestAbstract {
  private $_api;

  private $_sleepSeconds;

  private $_siteReference;

  private $_username;

  private $_password;

  private static $_defaultConfigArray = array( // Note - commented out config does not need to be set - just documenting all the possible config options somewhere.
    //'username' => '',
    //'password' => '',
    //'proxy_host' => '',
    //'proxy_port' => '',
    'ssl_verify_peer' => false,
    'ssl_verify_host' => 0,
    'ssl_cacertfile' => '',
    //'connect_timeout' => '',
    //'timeout' => '',
    //'connect_attempts' => '',
    //'sleep_useconds' => '',
    //'curl_options' => array(),

    //'datacenterurl' => '',
    //'jsonversion' => '',

    //'log_filename' => '',
    //'log_filepath' => '',
    //'log_archive_filepath' => '',
    //;log_level' => '',
  );

  public function getDefaultTransactionData($requestType) {
    $data = array(
      'AUTH' => array(
	'accounttypedescription' => 'ECOM',
	'currencyiso3a' => 'GBP',
	'baseamount' => '100',
      ),
      'CACHETOKENISE' => array(
        'pan' => '4111110000000211',
	'expirymonth' => '11',
	'expiryyear' => '2031',
	'securitycode' => '123',
	'paymenttypedescription' => 'VISA',
      ),
      'STORE' => array(
	'accounttypedescription' => 'CARDSTORE',
      ),
      'ORDER' => array(
	'accounttypedescription' => 'ECOM',
	'paymenttypedescription' => 'PAYPAL',
	'customerfirstname' => 'Tester',
	'customerlastname' => 'Jones',
	'customerpremise' => '1234',
	'customertown' => 'Bangor',
	'customercountryiso2a' => 'GB',
	'paypalemail' => 'test@example.com',
	'paypaladdressoverride' => '1',
	'returnurl' => 'www.example.com/return',
	'cancelurl' => 'www.example.com/cancel',
	'currencyiso3a' => 'GBP',
	'baseamount' => '100',
      ),
      'ORDERDETAILS' => array(),
      'ACCOUNTCHECK' => array(
	'accounttypedescription' => 'ECOM',
	'currencyiso3a' => 'GBP',
	'baseamount' => '0',
      ),
      'THREEDQUERY' => array(
	'accounttypedescription' => 'ECOM',
	'currencyiso3a' => 'GBP',
	'termurl' => 'https://www.termurl.com',
	'baseamount' => '100',
      ),
      'CURRENCYRATE' => array(
	'accounttypedescription' => 'CURRENCYRATE',
	'dcccurrencyiso3a' => 'USD',
	'dccbaseamount' => '100',
	'dcctype' => 'DCC',
      ),
      'RISKDEC' => array(
	'accounttypedescription' => 'FRAUDCONTROL',
	'currencyiso3a' => 'GBP',
	'baseamount' => '100',
      ),
      'IDSTANDARD' => array(
	'accounttypedescription' => 'IDENTITYCHECK',
	'customercountryiso2a' => 'GB',
	'customerfirstname' => 'John',
	'customerlastname' => 'Doe',
	'customerpremise' => '1',
	'customerpostcode' => 'AB12 3CD',
      ),
      'SUBSCRIPTION' => array(
	'subscriptionunit' => 'DAY',
	'subscriptiontype' => 'INSTALLMENT',
	'subscriptionfrequency' => '1',
	'subscriptionfinalnumber' => '1',
	'subscriptionnumber' => '1',
      ),
      'REFUND' => array(),
    );
    
    $returnData = $data[$requestType];
    $returnData['requesttypedescriptions'] = array($requestType);
    $returnData['sitereference'] = $this->_siteReference;
    return $returnData;
  }
						     
  private function _newInstance(array $configData) {
    return $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Api', $configData);
  }

  public function __construct($name = null, array $data = array(), $dataName = '') {
    parent::__construct($name, $data, $dataName);
    
    $testConfig = $this->_helper->parseIniFile(realpath(__DIR__ . '/../config.ini'), array('username', 'password', 'siteReference'));

    $this->_sleepSeconds = 2;
    $this->_siteReference = $testConfig['siteReference'];
    $this->_username = $testConfig['username'];
    $this->_password = $testConfig['password'];
    $this->_correctSslCertFile = $testConfig['correct_ssl_cert_file'] = $testConfig['correctSslCertFile'];
    $this->_incorrectSslCertFile = $testConfig['incorrect_ssl_cert_file'] = $testConfig['incorrectSslCertFile'];

    self::$_defaultConfigArray['username'] = $this->_username;
    self::$_defaultConfigArray['password'] = $this->_password;
  }

  public function setUp() {
    parent::setUp();

    $this->_ioc = \Securetrading\Stpp\JsonInterface\Main::bootstrapIoc();
    
    $this->_ioc->setOption('stpp_json_log_filename', 'json_log');
    $this->_ioc->setOption('stpp_json_log_filepath', $this->_testDir . 'logs' . DIRECTORY_SEPARATOR);
    $this->_ioc->setOption('stpp_json_log_archive_filepath', $this->_testDir . 'logs' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR);
  }

  private function _processRequest(array $configData, array $requestData) {    
    $request = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Request');
    $request->setMultiple($requestData);
    
    $api = $this->_newInstance($configData);
    $response = $api->process($request);

    $this->assertInstanceOf('\Securetrading\Stpp\JsonInterface\Response', $response);

    $actualResponseData = $response->toArray();

    return $actualResponseData;
  }

  private function _processRawRequest(array $configData, array $requestData) {        
    $api = $this->_newInstance($configData);
    $response = $api->process($requestData);

    $this->assertInstanceOf('\Securetrading\Stpp\JsonInterface\Response', $response);

    $actualResponseData = $response->toArray();

    return $actualResponseData;
  }

  private function _processRequests(array $configData, array $requestsData) {    
    $requests = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Requests');
    
    foreach($requestsData as $requestData) {
      $requestTypeDescription = $requestData['requesttypedescriptions'][0]; // Note - takes the first (and we assume, the only) entry from the requesttypedescriptions array), add it is a string requesttypedescription and delete the requesttypedescriptions.
      unset($requestData['requesttypedescriptions']);
      $requestData['requesttypedescription'] = $requestTypeDescription;
      
      $request = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Request');
      $request->setMultiple($requestData);
      $requests->addRequest($request);
    }
    
    $api = $this->_newInstance($configData);
    $response = $api->process($requests);

    $this->assertInstanceOf('\Securetrading\Stpp\JsonInterface\Response', $response);

    $actualResponseData = $response->toArray();
    
    return $actualResponseData;
  }

  private function _assertResponseData(array $expectedResponseData, array $actualResponseData) {
    foreach($expectedResponseData as $k => $v) {
      if (is_array($v)) {
	call_user_func(array($this, __METHOD__), $v, $actualResponseData[$k]);
      }
      else {
	$this->assertSame($v, $actualResponseData[$k], sprintf("Comparing values with key '%s'.", $k));
      }
    }
  }

  /**
   * @dataProvider providerCacheTokenise
   */
  public function testCacheTokenise($cacheTokenise) {
    list($configData, $requestData, $expectedResponseData) = $cacheTokenise;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerCacheTokenise() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
        $this->getDefaultTransactionData('CACHETOKENISE'),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	      'errormessage' => 'Ok',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerAuth
   */
  public function testAuth($auth) {
    list($configData, $requestData, $expectedResponseData) = $auth;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerAuth() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('AUTH'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	      'errormessage' => 'Ok',
	      'acquirerresponsecode' => '00',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerAuth_Moto
   */
  public function testAuth_Moto($auth) {
    list($configData, $requestData, $expectedResponseData) = $auth;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerAuth_Moto() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('AUTH'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	    'accounttypedescription' => 'MOTO',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	      'errormessage' => 'Ok',
	      'acquirerresponsecode' => '00',
	    ),
          ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerAuth_Decline
   */
  public function testAuth_Decline($auth) {
    list($configData, $requestData, $expectedResponseData) = $auth;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerAuth_Decline() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('AUTH'),
	  array(
	    'pan' => '4000000000000812',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '70000',	      
	      'errormessage' => 'Decline',
	      'authcode' => 'DECLINED',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerAuth_Sofort
   */
  public function testAuth_Sofort($auth) {
    list($configData, $requestData, $expectedResponseData) = $auth;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerAuth_Sofort() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('AUTH'),
	  array(
            'bankid' => '987654321',
	    'bankname' => 'FORTIS',
	    'currencyiso3a' => 'EUR',
	    'paymenttypedescription' => 'SOFORT',
	    'billingcountryiso2a' => 'DE',
	    'billingfirstname' => 'FIRSTNAME',
	    'billinglastname' => 'last1',
	    'billingpostcode' => 'AB45 6CB',
	    'billingpremise' => '789',
	    'billingstreet' => 'Street',
	    'billingtown' => 'Town',
        'successfulurlredirect' => 'https://trustpayments.com',
        'errorurlredirect' => 'https://trustpayments.com',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	      'errormessage' => 'Ok',
	      'settlestatus' => '10',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerAuth_Ach
   */
  public function testAuth_Ach($auth) {
    list($configData, $requestData, $expectedResponseData) = $auth;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerAuth_Ach() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('AUTH'),
	  array(
	    'achaba' => '987654321',
	    'achaccountnumber' => '123456781',
	    'achchecknumber' => '123456',
	    'achtype' => 'SAVINGS',
	    'paymenttypedescription' => 'ACH',
	    'currencyiso3a' => 'USD',
	    'billingcountryiso2a' => 'DE',
	    'billingfirstname' => 'FIRSTNAME',
	    'billinglastname' => 'last1',
	    'billingpostcode' => 'AB45 6CB',
	    'billingpremise' => '789',
	    'billingstreet' => 'Street',
	    'billingtown' => 'Town',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	      'errormessage' => 'Ok',
	      'acquirerresponsecode' => 'A01',
	      'acquirerresponsemessage' => 'APPROVED',
            ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerAuth_FromToken
   */
  public function testAuth_FromToken($cacheTokenise, $auth) {
    list($configData, $requestData, $expectedResponseData) = $cacheTokenise;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
    
    $cacheToken = $actualResponseData['responses'][0]['cachetoken'];
    
    sleep($this->_sleepSeconds);

    list($configData, $requestData, $expectedResponseData) = $auth;
    $requestData['cachetoken'] = $cacheToken;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerAuth_FromToken() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	$this->getDefaultTransactionData('CACHETOKENISE'),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	    ),
	  ),
        ),
      ),
      array(
        self::$_defaultConfigArray,
	$this->getDefaultTransactionData('AUTH'),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	      'acquirerresponsecode' => '00',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerStore
   */
  public function testStore($store) {
    list($configData, $requestData, $expectedResponseData) = $store;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerStore() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('STORE'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',		
	  )
        ),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerOrder
   */
  public function testOrder($order) {
    list($configData, $requestData, $expectedResponseData) = $order;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerOrder() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
        $this->getDefaultTransactionData('ORDER'),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerOrderDetails
   */
  public function testOrderDetails($order, $orderDetails) {
    list($configData, $requestData, $expectedResponseData) = $order;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);

    $transactionReference = $actualResponseData['responses'][0]['transactionreference'];

    sleep($this->_sleepSeconds);

    list($configData, $requestData, $expectedResponseData) = $orderDetails;
    $requestData['parenttransactionreference'] = $transactionReference;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerOrderDetails() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
        $this->getDefaultTransactionData('ORDER'),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	    ),
	  ),
        ),
      ),
      array(
        self::$_defaultConfigArray,
        $this->getDefaultTransactionData('ORDERDETAILS'),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	      'paypaladdressstatus' => 'Confirmed',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerAccountCheck
   */
  public function testAccountCheck($accountCheck) {
    list($configData, $requestData, $expectedResponseData) = $accountCheck;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerAccountCheck() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('ACCOUNTCHECK'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	    ),
	  ),
	),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerThreedQuery
   */
  public function testThreedQuery($threedQuery) {
    list($configData, $requestData, $expectedResponseData) = $threedQuery;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerThreedQuery() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('THREEDQUERY'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	    ),
	  ),
	),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerCurrencyRate
   */
  public function testCurrencyRate($currencyRate) {
    list($configData, $requestData, $expectedResponseData) = $currencyRate;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerCurrencyRate() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('CURRENCYRATE'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	    ),
	  ),
	),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerRiskDec
   */
  public function testRiskDec($riskDec) {
    list($configData, $requestData, $expectedResponseData) = $riskDec;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerRiskDec() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('RISKDEC'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	    ),
	  ),
	),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerTransactionQuery
   */
  public function testTransactionQuery($pendingAuth, $transactionQuery) {
    list($configData, $requestData, $expectedResponseData) = $pendingAuth;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
    
    $transactionReference = $actualResponseData['responses'][0]['transactionreference'];

    sleep($this->_sleepSeconds);
    
    list($configData, $requestData, $expectedResponseData) = $transactionQuery;
    $requestData['filter']['transactionreference'] = array('value' => $transactionReference, 'symbol' => 'IS');
    $expectedResponseData['responses'][0]['records'][0]['transactionreference'] = $transactionReference;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerTransactionQuery() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('AUTH'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	    ),
	  ),
        ),
      ),
      array(
        self::$_defaultConfigArray,
	array(
	  'requesttypedescriptions' => array('TRANSACTIONQUERY'),
	  'filter' => array(),
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	      'records' => array(
		array(
		  'requesttypedescription' => 'AUTH',
		  'interface' => 'PASS-JSON-JSON',
		  'acquirerresponsecode' => '00',
		),
	      ),
            ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerTransactionUpdate
   */
  public function testTransactionUpdate($pendingAuth, $transactionUpdate) {
    list($configData, $requestData, $expectedResponseData) = $pendingAuth;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
    
    $transactionReference = $actualResponseData['responses'][0]['transactionreference'];

    sleep($this->_sleepSeconds);
    
    list($configData, $requestData, $expectedResponseData) = $transactionUpdate;
    $requestData['filter']['transactionreference'] = array('value' => $transactionReference, 'symbol' => 'IS');
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerTransactionUpdate() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('AUTH'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	    ),
	  ),
        ),
      ),
      array(
        self::$_defaultConfigArray,
	array(
	  'requesttypedescriptions' => array('TRANSACTIONUPDATE'),
	  'filter' => array(),
	  'updates' => array('settlebaseamount' => '50'),
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerMulti_Accountcheck_Subscription
   */
  public function testMulti_Accountcheck_Subscription($threedquery_Subscription) {
    list($configData, $requestData, $expectedResponseData) = $threedquery_Subscription;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerMulti_Accountcheck_Subscription() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
	  $this->getDefaultTransactionData('ACCOUNTCHECK'),
	  $this->getDefaultTransactionData('SUBSCRIPTION'),
	  array(
	    'requesttypedescriptions' => array('ACCOUNTCHECK', 'SUBSCRIPTION'),
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	    'baseamount' => '100',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	      'requesttypedescription' => 'ACCOUNTCHECK',
	    ),
	    array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	      'requesttypedescription' => 'SUBSCRIPTION',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerSeparate_Accountcheck_Subscription
   */
  public function testSeparate_Accountcheck_Subscription($threedquery_Subscription) {
    list($configData, $requestData, $expectedResponseData) = $threedquery_Subscription;
    $actualResponseData = $this->_processRequests($configData, $requestData); // Note - Plural instead of singular.
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerSeparate_Accountcheck_Subscription() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array(
	  array_merge(
	    $this->getDefaultTransactionData('ACCOUNTCHECK'),
	    array(
	      'pan' => '4111110000000211',
	      'expirymonth' => '11',
	      'expiryyear' => '2031',
	      'securitycode' => '123',
	      'paymenttypedescription' => 'VISA',
	      'baseamount' => '100',			    
	    )
	  ),
	  $this->getDefaultTransactionData('SUBSCRIPTION'),
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	      'requesttypedescription' => 'ACCOUNTCHECK',
	    ),
	    array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	      'requesttypedescription' => 'SUBSCRIPTION',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerRefund
   */
  public function testRefund($order, $paypalAuth, $refund) {
    list($configData, $requestData, $expectedResponseData) = $order;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
    
    $transactionReference = $actualResponseData['responses'][0]['transactionreference'];
    $paypalToken = $actualResponseData['responses'][0]['paypaltoken'];
    $payerId = substr(md5($paypalToken), 0, 10) . "pid";

    sleep($this->_sleepSeconds);
    
    list($configData, $requestData, $expectedResponseData) = $paypalAuth;
    $requestData['parenttransactionreference'] = $transactionReference;
    $requestData['paypaltoken'] = $paypalToken;
    $requestData['paypalpayerid'] = $payerId;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
    $transactionReference = $actualResponseData['responses'][0]['transactionreference'];

    sleep($this->_sleepSeconds);

    list($configData, $requestData, $expectedResponseData) = $refund;
    $requestData['parenttransactionreference'] = $transactionReference;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerRefund() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	$this->getDefaultTransactionData('ORDER'),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	    ),
	  ),
        ),
      ),
      array(
        self::$_defaultConfigArray,
	array(
	  'sitereference' => $this->_siteReference,
	  'requesttypedescription' => 'AUTH',
	  'settlestatus' => '100',
	  'paymenttypedescription' => 'PAYPAL',
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	    ),
	  ),
        ),
      ),
      array(
        self::$_defaultConfigArray,
	$this->getDefaultTransactionData('REFUND'),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerRefundCft
   */
  public function testRefundCft($refundCft) {
    list($configData, $requestData, $expectedResponseData) = $refundCft;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerRefundCft() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('REFUND'),
	  array(
	    'accounttypedescription' => 'CFT',
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'baseamount' => '500',
	    'currencyiso3a' => 'GBP',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	      'errormessage' => 'Ok',
	      'authcode' => 'TEST REFUND ACCEPTED',
	      'acquirerresponsecode' => '00',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerMulti_Threedquery_Auth_Enrolled
   */
  public function testMulti_Threedquery_Auth_Enrolled($threedqueryAuth) {
    list($configData, $requestData, $expectedResponseData) = $threedqueryAuth;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerMulti_Threedquery_Auth_Enrolled() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
	  $this->getDefaultTransactionData('THREEDQUERY'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	    'requesttypedescriptions' => array('THREEDQUERY', 'AUTH'),
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	      'errormessage' => 'Ok',
	      'enrolled' => 'Y',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerMulti_Threedquery_Auth_NotEnrolled
   */
  public function testMulti_Threedquery_Auth_NotEnrolled($threedqueryAuth) {
    list($configData, $requestData, $expectedResponseData) = $threedqueryAuth;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerMulti_Threedquery_Auth_NotEnrolled() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
	  $this->getDefaultTransactionData('THREEDQUERY'),
	  array(
	    'pan' => '4000000000000721', # Note - Forces N-Enrolled.
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	    'requesttypedescriptions' => array('THREEDQUERY', 'AUTH'),
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	      'enrolled' => 'N',
	    ),
	    array(
	      'errorcode' => '0',
	      'errormessage' => 'Ok',
	      'acquirerresponsecode' => '00',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerInvalidRequest_NotUsingRequestObject
   */
  public function testInvalidRequest_NotUsingRequestObject($cacheTokenise) { // Note - Data provider works differently in this test case.
    list($configData, $expectedResponseData) = $cacheTokenise;

    $request = new \stdClass();
    // Note - Start modified from self::_processRequest().
    $api = $this->_newInstance($configData);
    $response = $api->process($request);

    $this->assertInstanceOf('\Securetrading\Stpp\JsonInterface\Response', $response);

    $actualResponseData = $response->toArray();
    // Note - End modified from self::_processRequest().

    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerInvalidRequest_NotUsingRequestObject() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array(
	  'responses' => array(
            array(
	      'errorcode' => '10',
	      'errormessage' => 'Incorrect usage of the Secure Trading API',
	      'errordata' => array('Invalid request type.'),
	      'requesttypedescription' => 'ERROR',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  // Note - Not implementing "test_invalid_request_using_own_object" from the Python library.  Do not believe it is necessary for the PHP lib since we assert the input request type.

  /**
   * @dataProvider providerInvalidRequest_NoData
   */
  public function testInvalidRequest_NoData($cacheTokenise) {
    list($configData, $requestData, $expectedResponseData) = $cacheTokenise;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerInvalidRequest_NoData() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
        array(),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '60018',
	      'errormessage' => 'Invalid requesttype',
	      'errordata' => array(),
	      'requesttypedescription' => 'ERROR',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerInvalidRequest_InvalidCredentials
   */
  public function testInvalidRequest_InvalidCredentials($cacheTokenise) {
    list($configData, $requestData, $expectedResponseData) = $cacheTokenise;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerInvalidRequest_InvalidCredentials() {
    $this->_addDataSet(
      array(
	array_merge(
          self::$_defaultConfigArray,
	  array(
	    'username' =>     $this->_username,
	    'password' => 'PASSWORD',
	  )
	),
        $this->getDefaultTransactionData('CACHETOKENISE'),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '6',
	      'errormessage' => 'Invalid credentials provided',
	      'errordata' => array('HTTP code 401.'),
	      'requesttypedescription' => 'ERROR',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * 
   */
  public function testRequest_WithInvalidUtf8RequestData() {
    $configData = self::$_defaultConfigArray;
    $requestData = array_merge(
      $this->getDefaultTransactionData('AUTH'),
      array(
	'pan' => '4111110000000211',
	'expirymonth' => '11',
	'expiryyear' => '2031',
	'securitycode' => '123',
	'paymenttypedescription' => 'VISA',
	'billingfirstname' => "\xC0\x00", # A leading UTF-8 byte without a valid continuation byte.
      )
    );
    $expectedResponseData = array(
      'responses' => array(
        array(
	  'errorcode' => '9',
	  'errormessage' => 'Unknown error. If this persists please contact Secure Trading',
	),
      )
    );
    
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  /**
   * @dataProvider providerSslVerification
   */
  public function testSslVerification($rootCertificateFile, $expectedResponseData) {
    $configData = array_replace_recursive(
      self::$_defaultConfigArray,
      array(
	'ssl_verify_peer' => true,
	'ssl_cacertfile' => $rootCertificateFile,
      )
    );

    $requestData = array_merge(
      $this->getDefaultTransactionData('AUTH'),
      array(
	'pan' => '4111110000000211',
	'expirymonth' => '11',
	'expiryyear' => '2031',
	'securitycode' => '123',
	'paymenttypedescription' => 'VISA',
      )
    );

    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerSslVerification() {
    $this->_addDataSet(
      $this->_correctSslCertFile,
      array(
        'responses' => array(
          array(
	    'errorcode' => '0',
	    'errormessage' => 'Ok',
	    'acquirerresponsecode' => '00',
	  ),
        )
      )
    );
    $this->_addDataSet(
      $this->_incorrectSslCertFile,
      array(
        'responses' => array(
          array(
	    'errorcode' => '8',
	    'errormessage' => 'Unexpected error connecting to Secure Trading servers. If the problem persists please contact support@securetrading.com',
	    'errordata' => array(),
	    'requesttypedescription' => 'ERROR',
	  ),
        )
      )
    );
    return $this->_getDataSets();
  }

  /**
   * 
   */
  public function testTranslationOfReturnedErrorMessage_WhenNoError() {
    $configData = array_replace_recursive(
      self::$_defaultConfigArray,
      array(
	'locale' => 'fr_fr',
      )
    );

    $requestData = array_merge(
      $this->getDefaultTransactionData('AUTH'),
      array(
	'pan' => '4111110000000211',
	'expirymonth' => '11',
	'expiryyear' => '2031',
	'securitycode' => '123',
	'paymenttypedescription' => 'VISA',
      )
    );

    $actualResponseData = $this->_processRequest($configData, $requestData);
    
    $expectedResponseData = array(
      'responses' => array(
	array(
	  'errorcode' => '0',
	  'errormessage' => 'OK', // Note - french translation now the same as the english.
	),
      )
    );

    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  /**
   * 
   */
  public function testTranslationOfReturnedErrorMessage_WhenUnknownError() {
    $configData = array_replace_recursive(
      self::$_defaultConfigArray,
      array(
	'locale' => 'fr_fr',
      )
    );

    $requestData = array_merge(
      $this->getDefaultTransactionData('AUTH'),
      array(
	'pan' => '4111110000000211',
	'expirymonth' => '11',
	'expiryyear' => '2031',
	'securitycode' => '123',
	'paymenttypedescription' => 'VISA',
      )
    );

    $request = new \stdClass();
    // Note - Start modified from self::_processRequest().
    $api = $this->_newInstance($configData);
    $response = $api->process($request);
    
    $this->assertInstanceOf('\Securetrading\Stpp\JsonInterface\Response', $response);

    $actualResponseData = $response->toArray();
    // Note - End modified from self::_processRequest().
    
    $expectedResponseData = array(
      'responses' => array(
	array(
	  'errorcode' => '10',
	  'errormessage' => 'Utilisation incorrecte de l\'API Secure Trading',
	),
      )
    );

    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  /**
   * @dataProvider providerTranslationOfReturnedErrorMessage_WhenMultipleResponses
   */
  public function testTranslationOfReturnedErrorMessage_WhenMultipleResponses($threedquery_Subscription) {
    list($configData, $requestData, $expectedResponseData) = $threedquery_Subscription;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerTranslationOfReturnedErrorMessage_WhenMultipleResponses() {
    $this->_addDataSet(
      array(
	array_replace_recursive(
          self::$_defaultConfigArray,
          array(
	    'locale' => 'fr_fr',
          )
	),
	array_merge(
	  $this->getDefaultTransactionData('ACCOUNTCHECK'),
	  $this->getDefaultTransactionData('SUBSCRIPTION'),
	  array(
	    'requesttypedescriptions' => array('ACCOUNTCHECK', 'SUBSCRIPTION'),
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	    'baseamount' => '100',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',
	      'errormessage' => 'OK', // Note - french translation now the same as the english.
	      'requesttypedescription' => 'ACCOUNTCHECK',
	    ),
	    array(
	      'errorcode' => '0',
	      'errormessage' => 'OK', // Note - french translation now the same as the english.
	      'requesttypedescription' => 'SUBSCRIPTION',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerErrorData_ContainsExceptionData_WhenUnexpectedExceptionCaught
   */
  public function testErrorData_ContainsExceptionData_WhenUnexpectedExceptionCaught($auth) {
    $mainExceptionMessage = 'Causing an exception to be thrown from within the try/catch of \Securetrading\Stpp\JsonInterface\Api::process() so we can examine the returned errordata.';

    $this->_ioc->set('\Securetrading\Stpp\JsonInterface\Converter', function(\Securetrading\Ioc\IocInterface $ioc, $alias, $params) use ($mainExceptionMessage) {
      $previousException = new \Exception('Previous exception message.');
      throw new \Exception($mainExceptionMessage, 0, $previousException);
    });

    list($configData, $requestData, $expectedResponseData) = $auth;
    $actualResponseData = $this->_processRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);

    $errorData = $actualResponseData['responses'][0]['errordata'];
    $this->assertEquals($mainExceptionMessage, $errorData[0]); # Exception message.
    $this->assertRegExp("/^.+ApiTest\.php$/", $errorData[1]); # Exception file.
    $this->assertTrue(is_int($errorData[2])); # Exception line number.
    $this->assertRegExp("/^#0 \[internal function\].+$/m", $errorData[3]); # Exception stack trace.
    $this->assertRegExp("/^.+Previous exception message\..+$/m", $errorData[4]); # Previous exception stack trace.
  }

  public function providerErrorData_ContainsExceptionData_WhenUnexpectedExceptionCaught() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('AUTH'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '9',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerLogging
   */
  public function testLogging($expectedLines, $logLevel = null) {
    if (null !== $logLevel) {
      $this->_ioc->setOption('stpp_json_log_level', $logLevel);
    }
   
    $requestData = array_merge(
      $this->getDefaultTransactionData('AUTH'),
      array(
	'pan' => '4111110000000211',
	'expirymonth' => '11',
	'expiryyear' => '2031',
	'securitycode' => '123',
	'paymenttypedescription' => 'VISA',
      )
    );

    $actualResponseData = $this->_processRequest(self::$_defaultConfigArray, $requestData);
    $this->assertEquals('0', $actualResponseData['responses'][0]['errorcode']);

    $contents = file_get_contents($this->_testDir . 'logs' . DIRECTORY_SEPARATOR . 'json_log.txt');
    $lines = explode(PHP_EOL, $contents);
    
    $this->assertEquals("", array_pop($lines)); # Remove the last line (it is an empty line; but assert this for sanity).

    $requestReferences = array();

    foreach($lines as $line) {
      $this->assertEquals(1, preg_match("/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2} [A-Z]{3} - [A-Z]+ - (A[0123456789abcdefghjkmnpqrtuvwxy]{8}) - .+$/", $line, $matches));
      $requestReferences[] = $matches[1];
    }

    $this->assertEquals(1, count(array_unique($requestReferences))); # Same requestreference used for all log entries in the transaction.

    $this->assertEquals(count($expectedLines), count($lines));

    foreach($expectedLines as $line) {
      $logLevel = preg_quote($line[0]);
      $logMessage = preg_quote($line[1]);
      $regex = "!.+${logLevel}.+${logMessage}!";
      $this->assertEquals(1, preg_match($regex, $contents), sprintf('Trying to match %s.', $regex));
    }
  }

  public function providerLogging() {
    $this->_addDataSet(array(
      array('DEBUG', 'Setting requestreference.'),
      array('DEBUG', 'Setting accounttypedescription.'),
      array('DEBUG', 'Setting currencyiso3a.'),
      array('DEBUG', 'Setting baseamount.'),
      array('DEBUG', 'Setting requesttypedescriptions.'),
      array('DEBUG', 'Setting sitereference.'),
      array('DEBUG', 'Setting pan.'),
      array('DEBUG', 'Setting expirymonth.'),
      array('DEBUG', 'Setting expiryyear.'),
      array('DEBUG', 'Setting securitycode.'),
      array('DEBUG', 'Setting paymenttypedescription.'),
      array('INFO', 'Starting request.'),
      array('DEBUG', 'Starting encoding.'),
      array('DEBUG', 'Instance of \Securetrading\Stpp\JsonInterface\Request detected.'),
      array('DEBUG', 'Finished encoding.'),
      array('INFO', 'Beginning HTTP request to https://webservices.securetrading.net/json/.'),
      array('INFO', 'Finished HTTP request to https://webservices.securetrading.net/json/.'),
      array('DEBUG', 'Starting decoding.'),
      array('DEBUG', 'Setting requestreference.'),
      array('DEBUG', 'Setting version.'),
      array('DEBUG', 'Setting responses.'),
      array('DEBUG', 'Finished decoding.'),
      array('INFO', 'Finished request.'),
    ), null);

    $this->_addDataSet(array(
      array('INFO', 'Starting request.'),
      array('INFO', 'Beginning HTTP request to https://webservices.securetrading.net/json/.'),
      array('INFO', 'Finished HTTP request to https://webservices.securetrading.net/json/.'),
      array('INFO', 'Finished request.'),
    ), \Securetrading\Log\Filter::INFO);
    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerRawRequest
   */
  public function testRawRequest($auth) {
    list($configData, $requestData, $expectedResponseData) = $auth;
    $actualResponseData = $this->_processRawRequest($configData, $requestData);
    $this->_assertResponseData($expectedResponseData, $actualResponseData);
  }

  public function providerRawRequest() {
    $this->_addDataSet(
      array(
        self::$_defaultConfigArray,
	array_merge(
          $this->getDefaultTransactionData('AUTH'),
	  array(
	    'pan' => '4111110000000211',
	    'expirymonth' => '11',
	    'expiryyear' => '2031',
	    'securitycode' => '123',
	    'paymenttypedescription' => 'VISA',
	  )
	),
	array(
	  'responses' => array(
            array(
	      'errorcode' => '0',	      
	      'errormessage' => 'Ok',
	      'acquirerresponsecode' => '00',
	    ),
	  ),
        ),
      )
    );    
    return $this->_getDataSets();
  }
}