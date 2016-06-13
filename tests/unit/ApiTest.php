<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

require_once(__DIR__ . '/helpers/CoreMocks.php');

class ApiTest extends \Securetrading\Unittest\UnittestAbstract {

  private $_api;

  private $_stubIoc;

  private $_stubConfig;

  private $_stubLog;

  protected function _newRequest() {
    $ioc = $this->getMock('\Securetrading\Ioc\Ioc');
    $ioc
      ->expects($this->any())
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($this->getMock('\Securetrading\Stpp\JsonInterface\Log'))
    ;
    return new \Securetrading\Stpp\JsonInterface\Request($ioc);
  }

  protected function _newRequests() {
    $ioc = $this->getMock('\Securetrading\Ioc\Ioc');
    $ioc
      ->expects($this->any())
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($this->getMock('\Securetrading\Stpp\JsonInterface\Log'))
    ;
    return new \Securetrading\Stpp\JsonInterface\Requests($ioc);
  }

  public function setUp() {
    $this->_stubIoc = $this->getMock('\Securetrading\Ioc\Ioc');
    $this->_stubConfig = $this->getMock('\Securetrading\Stpp\JsonInterface\Config');
    $this->_stubLog = $this->getMock('\Securetrading\Stpp\JsonInterface\Log');

    $this->_stubIoc
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($this->_stubLog)
    ;

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
    $stubExceptionMapper = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\ExceptionMapper')->disableOriginalConstructor()->getMock();
    $stubRequest = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Request')->disableOriginalConstructor()->getMock();
    $response = new \Securetrading\Stpp\JsonInterface\Response($this->_stubIoc);
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

    $stubRequest
      ->method('getSingle')
      ->will($this->throwException(new \Exception('exception_message', 5)))
    ;

    $this->_stubLog
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
    $this->_addDataSet(new \stdClass());
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
    $stubResponse = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Response')->disableOriginalConstructor()->getMock();
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
    $stubResponse = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Response')->disableOriginalConstructor()->getMock();
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

    $responseObject = new \Securetrading\Stpp\JsonInterface\Response($this->_stubIoc);

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
    $actualReturnValue = $this->_($this->_api, '_getLog');
    $this->assertSame($this->_stubLog, $actualReturnValue);
  }
}
