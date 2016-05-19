<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

require_once(__DIR__ . '/helpers/CoreMocks.php');

class ApiTest extends \Securetrading\Unittest\UnittestAbstract {

  private $_api;

  private $_stubIoc;

  private $_stubConfig;
  
  protected function _newRequest() {
    $ioc = $this->getMock('\Securetrading\Ioc\Ioc');
    $ioc
      ->expects($this->once())
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($this->getMock('\Securetrading\Stpp\JsonInterface\Log'))
    ;
    return new \Securetrading\Stpp\JsonInterface\Request($ioc);
  }

  protected function _newRequests() {
    $ioc = $this->getMock('\Securetrading\Ioc\Ioc');
    $ioc
      ->expects($this->once())
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($this->getMock('\Securetrading\Stpp\JsonInterface\Log'))
    ;
    return new \Securetrading\Stpp\JsonInterface\Requests($ioc);
  }

  public function setUp() {
    $this->_stubIoc = $this->getMock('\Securetrading\Ioc\Ioc');
    $this->_stubConfig = $this->getMock('\Securetrading\Stpp\JsonInterface\Config');

    $this->_api = new \Securetrading\Stpp\JsonInterface\Api($this->_stubIoc, $this->_stubConfig);
  }

  public function tearDown() {
    \Securetrading\Unittest\CoreMocker::releaseCoreMocks();
  }

  // Note - Api::process() not unit tested; better suited for - and well covered by - integration tests.

  /**
   * 
   */
  public function testProcess_WhenExceptionCaught_Logs() {
    $logMock = $this->getMock('\Securetrading\Stpp\JsonInterface\Log');
    $stubExceptionMapper = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\ExceptionMapper')->disableOriginalConstructor()->getMock();
    $stubRequest = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Request')->disableOriginalConstructor()->getMock();
    $response = new \Securetrading\Stpp\JsonInterface\Response();
    $translatorStub = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Translator')->disableOriginalConstructor()->getMock();

    $returnValueMap = array(
      array('\Securetrading\Stpp\JsonInterface\ExceptionMapper', array(), $stubExceptionMapper),
      array('\Securetrading\Stpp\JsonInterface\Response', array(), $response),
      array('\Securetrading\Stpp\JsonInterface\Translator', array('config' => $this->_stubConfig), $translatorStub),
    );

    $this->_stubIoc
      ->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($returnValueMap));
    ;
    
    $this->_stubIoc
      ->expects($this->any())
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($logMock)
    ;

    $stubRequest
      ->method('getSingle')
      ->will($this->throwException(new \Exception('exception_message', 5)))
    ;

    $logMock
      ->expects($this->exactly(2))
      ->method('alert')
      ->withConsecutive(
        array($this->matchesRegularExpression('/^Exception of type Exception caught with code 5 in .+ on line \d+: "exception_message".$/')),
	array($this->isInstanceOf('\Exception'))
      )
    ;

    $this->_api->process($stubRequest);
  }

  /**
   * @dataProvider provider_verifyRequest
   */
  public function test_verifyRequest($request) {
    $returnValue = $this->_($this->_api, '_verifyRequest', $request);
    $this->assertSame($request, $returnValue);
  }

  public function provider_verifyRequest() {
    $this->_addDataSet($this->_newRequest());
    $this->_addDataSet($this->_newRequests());
    return $this->_getDataSets();
  }

  /**
   * 
   */
  public function test_verifyRequest_WhenArrayGiven() {    
    $mockedRequest = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Request')->disableOriginalConstructor()->getMock();
    $mockedRequest
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo(array('key' => 'value')))
    ;

    $this->_stubIoc
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Request'))
      ->willReturn($mockedRequest)
    ;

    $returnValue = $this->_($this->_api, '_verifyRequest', array('key' => 'value'));
    $this->assertSame($mockedRequest, $returnValue);
  }

  /**
   * @dataProvider provider_verifyRequest_WithInvalidRequest
   * @expectedException \Securetrading\Stpp\JsonInterface\ApiException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\ApiException::CODE_INVALID_REQUEST_TYPE
   */
  public function test_verifyRequest_WithInvalidRequest($request) {
    $returnValue = $this->_($this->_api, '_verifyRequest', $request);
  }

  public function provider_verifyRequest_WithInvalidRequest() {
    $this->_addDataSet(new \Securetrading\Stpp\JsonInterface\Response());
    return $this->_getDataSets();
  }

  /**
   * @dataProvider provider_convertData_WithStringAndArray
   */
  public function test_convertData_WithStringAndArray($inputData, $expectedReturnValue) {
    \Securetrading\Unittest\CoreMocker::mockCoreFunction('iconv', function($inputCharacterEncoding, $outputCharacterEncoding, $string) {
      return strtoupper($string);
    });
    $actualReturnValue = $this->_($this->_api, '_convertData', $inputData);
    $this->assertEquals($expectedReturnValue, $actualReturnValue);
  }

  public function provider_convertData_WithStringAndArray() {
    $req = $this->_newRequest();
    $req->setSingle('a', 'b');
    $this->_addDataSet('a', 'A');
    $this->_addDataSet(array('a', 'b','c'), array('A', 'B', 'C'));
    return $this->_getDataSets();
  }

  /**
   * @dataProvider provider_convertData_WithRequestObject
   */
  public function test_convertData_WithRequestObject($inputRequest, $expectedData) {
    \Securetrading\Unittest\CoreMocker::mockCoreFunction('iconv', function($inputCharacterEncoding, $outputCharacterEncoding, $string) {
      return strtoupper($string);
    });
    $returnValue = $this->_($this->_api, '_convertData', $inputRequest);
    $actualData = $returnValue->toArray();
    unset($actualData['requestreference']);
    $this->assertEquals($expectedData, $actualData);
  }

  public function provider_convertData_WithRequestObject() {
    $request = $this->_newRequest();
    $request->setSingle('a', 'b');
    $request->setSingle('c', array('d', 'e'));
    $this->_addDataSet($request, array('a' => 'B', 'c' => array('D', 'E')));
    return $this->_getDataSets();
  }

  /**
   * @dataProvider provider_convertCharacterEncodingOfRequest_WithRequestsObject
   */
  public function test_convertCharacterEncodingOfRequest_WithRequestsObject($inputRequests, $expectedRequest1Data, $expectedRequest2Data) {
    \Securetrading\Unittest\CoreMocker::mockCoreFunction('iconv', function($inputCharacterEncoding, $outputCharacterEncoding, $string) {
      return strtoupper($string);
    });
    $returnValue = $this->_($this->_api, '_convertCharacterEncodingOfRequest', $inputRequests);
    $inputRequestsArray = $inputRequests->getRequests();
    $actualRequest1Data = $inputRequestsArray[0]->toArray();
    $actualRequest2Data = $inputRequestsArray[1]->toArray();
    unset($actualRequest1Data['requestreference']);
    unset($actualRequest2Data['requestreference']);
    $this->assertEquals($expectedRequest1Data, $actualRequest1Data);
    $this->assertEquals($expectedRequest2Data, $actualRequest2Data);
  }

  public function provider_convertCharacterEncodingOfRequest_WithRequestsObject() {
    $request1 = $this->_newRequest();
    $request1->setSingle('a', 'b');
    $request2 = $this->_newRequest();
    $request2->setSingle('c', 'd');
    $requests = $this->_newRequests();
    $requests->addRequest($request1);
    $requests->addRequest($request2);
    $this->_addDataSet($requests, array('a' => 'B'), array('c' => 'D'));
    return $this->_getDataSets();
  }

  /**
   * @dataProvider provider_convertCharacterEncodingOfRequest_WithRequestObject
   */
  public function test_convertCharacterEncodingOfRequest_WithRequestObject($inputRequest, $expectedData) {
    \Securetrading\Unittest\CoreMocker::mockCoreFunction('iconv', function($inputCharacterEncoding, $outputCharacterEncoding, $string) {
      return strtoupper($string);
    });
    $this->_($this->_api, '_convertCharacterEncodingOfRequest', $inputRequest);
    $actualData = $inputRequest->toArray();
    unset($actualData['requestreference']);
    $this->assertEquals($expectedData, $actualData);
  }

  public function provider_convertCharacterEncodingOfRequest_WithRequestObject() {
    $request = $this->_newRequest();
    $request->setSingle('a', 'b');
    $request->setSingle('c', array('d', 'e'));
    $this->_addDataSet($request, array('a' => 'B', 'c' => array('D', 'E')));
    return $this->_getDataSets();
  }

  /**
   *
   */
  public function test_getUrl() {
    $this->_stubConfig
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('datacenterurl'))
      ->willReturn('default_datacenterurl')
    ;

    $stubRequest = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Request')->disableOriginalConstructor()->getMock();
    $stubRequest
      ->expects($this->once())
      ->method('getSingle')
      ->with($this->equalTo('datacenterurl'), $this->equalTo('default_datacenterurl'))
      ->willReturn('url/')
    ;

    $returnValue = $this->_($this->_api, '_getUrl', $stubRequest);
    $this->assertEquals('url/json/', $returnValue);
  }

  /**
   * 
   */
  public function test_verifyResult() {
    $stubResponse = $this->getMock('\Securetrading\Stpp\JsonInterface\Response');
    $stubResponse
      ->method('getSingle')
      ->with($this->equalTo('requestreference'))
      ->willReturn('REQUEST_REFERENCE')
    ;

    $this->_($this->_api, '_verifyResult', $stubResponse, 'REQUEST_REFERENCE');    

    $this->assertTrue(true); # Dummy assertion; this test ensures no exceptions are thrown.
  }

  /**
   * @expectedException \Securetrading\Stpp\JsonInterface\ApiException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\ApiException::CODE_MISMATCHING_REQUEST_REFERENCE
   */
  public function test_verifyResult_WithMismatchingRequestReferences() {
    $stubResponse = $this->getMock('\Securetrading\Stpp\JsonInterface\Response');
    $stubResponse
      ->method('getSingle')
      ->with($this->equalTo('requestreference'))
      ->willReturn('RESPONSE_REQUEST_REFERENCE')
    ;

    $this->_($this->_api, '_verifyResult', $stubResponse, 'REQUEST_REFERENCE');
  }

  /**
   * 
   */
  public function test_generateError() {
    $exceptionMapperStub = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\ExceptionMapper')->disableOriginalConstructor()->getMock();

    $responseObject = new \Securetrading\Stpp\JsonInterface\Response();

    $returnValueMap = array(
      array('\Securetrading\Stpp\JsonInterface\ExceptionMapper', array(), $exceptionMapperStub),
      array('\Securetrading\Stpp\JsonInterface\Response', array(), $responseObject),
    );

    $this->_stubIoc
      ->method('get')
      ->will($this->returnValueMap($returnValueMap))
    ;

    $stubException = new \Exception();

    $exceptionMapperStub
      ->expects($this->once())
      ->method('getOutputErrorCodeAndData')
      ->with($this->equalTo($stubException))
      ->willReturn(array('30001',  array('error', 'data')))
    ;

    $expectedResponseData = array(
      'requestreference' => 'REQUEST_REFERENCE',
      'responses' => array(
        array(
          'errorcode' => '30001',
	  'errordata' => array('error', 'data'),
	  'requesttypedescription' => 'ERROR',
	  'requestreference' => 'REQUEST_REFERENCE',
	),
      ),
    );

    $actualReturnValue = $this->_($this->_api, '_generateError', $stubException, 'REQUEST_REFERENCE');

    $this->assertSame($responseObject, $actualReturnValue);
    $this->assertEquals($expectedResponseData, $responseObject->toArray());
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

    $actualReturnValue = $this->_($this->_api, '_getLog');

    $this->assertEquals('dummy_return_log_object', $actualReturnValue);
  }
}
