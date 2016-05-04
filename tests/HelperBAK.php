<?php

namespace Securetrading\Stpp\JsonInterface\Tests;

class Helper {# extends \Securetrading\Unittest\IntegrationtestAbstract {
  private $_specialCharsString = "T\r\xc2\xa3S'T(|]><[\\xG %s %% \"+N\\\\\&\\M\xc8.?\nTAB\t12}34{56789,:;#END";

  private $_ioc;

  private $_username;
  
  private $_password;

  private $_siteReference;

  public function __construct(\Securetrading\Ioc\IocInterface $ioc, $siteReference) {
    $this->_ioc = $ioc;
    $this->_siteReference = $siteReference;
  }

  public function setUsername($username) {
    $this->_username = $username;
    return $this;
  }
  public function setPassword($password) {
    $this->_password = $password;
  }

  public function getDefaultConfig() {
    return array( // Note - commented out config does not need to be set - just documenting all the possible config options somewhere.
      'connections' => array(
	'json_interface' => array(
          'username' => $this->_username,
	  'password' => $this->_password,
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
	),
      ),
      'json_interface' => array(
	//'datacenterurl' => '',
	//'jsonversion' => '',
	//'input_encoding' => '',
      ),
    );
  }

  public function getDefaultTransactionData($requestType) {
    $data = array(
      'AUTH' => array(
	'accounttypedescription' => 'ECOM',
	'currencyiso3a' => 'GBP',
	'baseamount' => '100',
	'customerfirstname' => $this->_specialCharsString,
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
	'billingfirstname' => $this->_specialCharsString,
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
	'customerfirstname' => $this->_specialCharsString,
      ),
      'THREEDQUERY' => array(
	'accounttypedescription' => 'ECOM',
	'currencyiso3a' => 'GBP',
	'termurl' => 'https://www.termurl.com',
	'baseamount' => '100',
	'customerfirstname' => $this->_specialCharsString,
      ),
      'CURRENCYRATE' => array(
	'accounttypedescription' => 'CURRENCYRATE',
	'dcccurrencyiso3a' => 'USD',
	'dccbaseamount' => '100',
	'dcctype' => 'DCC',
	'customerfirstname' => $this->_specialCharsString,
      ),
      'RISKDEC' => array(
	'accounttypedescription' => 'FRAUDCONTROL',
	'currencyiso3a' => 'GBP',
	'baseamount' => '100',
	'customerfirstname' => $this->_specialCharsString,
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

  public function newInstance(array $configData) {
    return $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Api', $configData);
  }

  public function processRequest(array $configData, array $requestData) {
    $request = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Request');
    $request->setMultiple($requestData);

    $api = $this->newInstance($configData);
    $response = $api->process($request);
    #var_dump($response->toArray());exit;
    #$this->assertInstanceOf('\Securetrading\Stpp\JsonInterface\Response', $response);#TODO-!!

    $actualResponseData = $response->toArray();
    #var_dump($actualResponseData);exit;
    return $actualResponseData;
  }

  public function processRequests(array $configData, array $requestsData) {    
    $requests = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Requests');
    
    foreach($requestsData as $requestData) {
      $requestTypeDescription = $requestData['requesttypedescriptions'][0]; // Note - takes the first (and we assume, the only) entry from the requesttypedescriptions array), add it is a string requesttypedescription and delete the requesttypedescriptions.
      unset($requestData['requesttypedescriptions']);
      $requestData['requesttypedescription'] = $requestTypeDescription;
      
      $request = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Request');
      $request->setMultiple($requestData);
      $requests->addRequest($request);
    }
    
    $api = $this->newInstance($configData);
    $response = $api->process($requests);

    #$this->assertInstanceOf('\Securetrading\Stpp\JsonInterface\Response', $response);#TODO-!!

    $actualResponseData = $response->toArray();
    
    return $actualResponseData;
  }
}