<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

class ConverterTest extends \Securetrading\Unittest\UnittestAbstract {
  private $_converter;

  private $_stubConfig;
  
  private $_stubIoc;

  public function setUp() {
    $this->_stubConfig = $this->getMock('\Securetrading\Stpp\JsonInterface\Config');
    $this->_stubIoc = $this->getMock('\Securetrading\Ioc\Ioc');
    $this->_stubLog = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
    $this->_converter = new \Securetrading\Stpp\JsonInterface\Converter($this->_stubConfig, $this->_stubIoc);
  }

  private function _stubIocToReturnLog() {
    $this->_stubIoc
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($this->_stubLog)
    ;
  }

  /**
   * @dataProvider providerEncode
   */
  public function testEncode($configMap, $request, $expectedReturnValue) {
    $this->_stubIocToReturnLog();
    $this->_stubConfig->method('get')->will($this->returnValueMap($configMap));

    $actualReturnValue = $this->_converter->encode($request);
    $this->assertSame($expectedReturnValue, $actualReturnValue);
  }

  public function providerEncode() {
    $configMap = array(
      array('username', 'username'),
      array('jsonversion', 'json_version'),
    );
    $dummyRequest = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Request')->disableOriginalConstructor()->getMock();
    $dummyRequest->method('toArray')->willReturn(array('a' => 'b'));
    $this->_addDataSet(
      $configMap,
      $dummyRequest, 
      '{"alias":"username","version":"json_version","request":[{"a":"b"}],"libraryversion":"php_1.0.0"}'
    );

    $configMap = array(
      array('username', 'username2'),
      array('jsonversion', 'json_version2'),
    );
    $dummyRequest1 = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Request')->disableOriginalConstructor()->getMock();
    $dummyRequest1->method('toArray')->willReturn(array('a' => 'b'));
    $dummyRequest2 = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Request')->disableOriginalConstructor()->getMock();
    $dummyRequest2->method('toArray')->willReturn(array('c' => 'd'));
    $dummyRequests = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Requests')->disableOriginalConstructor()->getMock();
    $dummyRequests->method('getRequests')->willReturn(array($dummyRequest1, $dummyRequest2));
    $this->_addDataSet(
      $configMap,
      $dummyRequests,
      '{"alias":"username2","version":"json_version2","request":[{"a":"b"},{"c":"d"}],"libraryversion":"php_1.0.0"}'
    );

    // Note - The Python library has additional test cases for: multiple requests in one Request object.  Single request in a Requests object.  The PHP library doesn't need them becaus of slightly different source and test logic.
    
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerEncode_Logging
   */
  public function testEncode_Logging($stubRequest, $logMessage) {
    $this->_stubIocToReturnLog();

    $this->_stubLog
      ->expects($this->any())
      ->method('debug')
      ->withConsecutive(
        array($this->equalTo('Starting encoding.')),
        array($this->equalTo($logMessage)),
	array($this->equalTo('Finished encoding.'))
      )
    ;

    $this->_converter->encode($stubRequest);
  }

  public function providerEncode_Logging() {
    $requestStub = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Request')->disableOriginalConstructor()->getMock();

    $requestsStub = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Requests')->disableOriginalConstructor()->getMock();
    $requestsStub->method('getRequests')->willReturn(array());

    $this->_addDataSet($requestStub, 'Instance of \Securetrading\Stpp\JsonInterface\Request detected.');
    $this->_addDataSet($requestsStub, 'Instance of \Securetrading\Stpp\JsonInterface\Requests detected.');
    return $this->_getDataSets();
  }

  /**
   * @dataProvider providerEncode_WithInvalidInput
   * @expectedException \Securetrading\Stpp\JsonInterface\ConverterException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\ConverterException::CODE_ENCODE_INVALID_REQUEST_TYPE
   */
  public function testEncode_WithInvalidInput($input) {
    $this->_stubIocToReturnLog();
    $this->_converter->encode($input);
  }

  public function providerEncode_WithInvalidInput() {
    $this->_addDataSet($this->getMockBuilder('\Securetrading\Stpp\JsonInterface\AbstractRequest')->disableOriginalConstructor()->getMockForAbstractClass());
    return $this->_getDataSets();
  }

  /**
   * @expectedException \Securetrading\Stpp\JsonInterface\ConverterException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\ConverterException::CODE_ENCODE_TO_JSON_FAILED
   */
  public function testEncode_WhenCannotJsonEncodeRequest() {
    $this->_stubIocToReturnLog();
    $dummyRequest = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Request')->disableOriginalConstructor()->getMock();
    $dummyRequest->method('toArray')->willReturn(chr(193)); // Invalid UTF-8 char.
    $this->_converter->encode($dummyRequest);
  }

  /**
   * @dataProvider providerDecode
   */
  public function testDecode($jsonStringResponse, $expectedResponseData) {
    $this->_stubIocToReturnLog();

    $responseObject = new \Securetrading\Stpp\JsonInterface\Response($this->_stubIoc);

    $this->_stubIoc
      ->method('get')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Response'))
      ->willReturn($responseObject)
    ;

    $returnValue = $this->_converter->decode($jsonStringResponse);

    $this->assertSame($expectedResponseData, $responseObject->toArray());    
  }

  public function providerDecode() {
    // AUTH:
    $this->_addDataSet('
      {
        "requestreference" : "Ahc6uwqq6",
        "version": "1.00",
        "response": [{
	  "errorcode": "0",
	  "requesttypedescription": "AUTH"
	}],
        "secrand": "kpEEso8"
      }
      ',
      array(
	'requestreference' => 'Ahc6uwqq6',
	'version' => '1.00',
	'responses' => array(
          array(
	    'errorcode' => '0',
	    'requesttypedescription' => 'AUTH',
	  ),
        ),
      )
    );

    // ACCOUNTCHECk, AUTH

    $this->_addDataSet('
      {
        "requestreference": "Arckthaau",
        "version": "1.00",
        "response": [{
	  "errorcode": "0",
	  "requesttypedescription": "ACCOUNTCHECK"
	},{
	  "errorcode": "0",
	  "requesttypedescription": "AUTH"
	}],
        "secrand": "0xto2lBEx"
      }
      ',
      array(
        'requestreference' => 'Arckthaau',
	'version' => '1.00',
	'responses' => array(
          array(
	    "errorcode" => "0",
	    "requesttypedescription" => "ACCOUNTCHECK",
          ),
	  array(
	    "errorcode" => "0",
	    "requesttypedescription" => "AUTH",
          )
	),
      )
    );

    // Bad reqest type

    $this->_addDataSet(
      '{
        "requestreference": "Armm51h6v",
        "version": "1.00",
        "response": [{
          "errorcode": "60018",
          "requesttypedescription": "ERROR",
          "errormessage": "Invalid requesttype",
          "errordata": ["BADREQUEST"]
        }],
        "secrand": "P35L"
      }
      ',
      array(
        'requestreference' => 'Armm51h6v',
	"version" => "1.00",
	"responses" => array(
	  array(
	    "errorcode" => "60018",
	    "requesttypedescription" => "ERROR",
	    "errormessage" => "Invalid requesttype",
	    "errordata" => array("BADREQUEST"),
	  ),
        ),
      )
    );

    return $this->_getDataSets();
  }

  /**
   * 
   */
  public function testDecode_Logging() {
    $this->_stubIocToReturnLog();

    $this->_stubIoc
      ->method('get')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Response'))
      ->willReturn($this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Response')->disableOriginalConstructor()->getMock())
    ;

    $this->_stubLog
      ->expects($this->any())
      ->method('debug')
      ->withConsecutive(
        array($this->equalTo('Starting decoding.')),
	array($this->equalTo('Finished decoding.'))
      )
    ;

    $responseStr = '{
        "requestreference" : "reqref",
        "version" : "ver",
        "response": [{}]
    }';
    
    $this->_converter->decode($responseStr);
  }

  /**
   * @expectedException \Securetrading\Stpp\JsonInterface\ConverterException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\ConverterException::CODE_DECODE_FROM_JSON_FAILED
   */
  public function testDecode_WhenCannotJsonDecodeRequest() {
    $this->_stubIocToReturnLog();
    $this->_converter->decode("BADJSONSTRING");
  }

  /**
   *
   */
  public function test_getLog() {
    $this->_stubIoc
      ->expects($this->once())
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn('dummy_return_log_object')
    ;

    $actualReturnValue = $this->_($this->_converter, '_getLog');

    $this->assertEquals('dummy_return_log_object', $actualReturnValue);
  }
}