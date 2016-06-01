<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

class HttpTest extends \Securetrading\Unittest\UnittestAbstract {
  private $_stubIoc;
  
  private $_stubConfig;

  private $_stubCurl;

  private $_http;

  public function setUp() {
    $this->_stubIoc = $this->getMock('\Securetrading\Ioc\IocInterface');
    $this->_stubConfig = $this->getMock('\Securetrading\Stpp\JsonInterface\Config');
    $this->_stubCurl = $this->getMockBuilder('\Securetrading\Http\Curl')->disableOriginalConstructor()->getMock();
    $this->_http = new \Securetrading\Stpp\JsonInterface\Http($this->_stubIoc, $this->_stubConfig);

    $this->_stubConfig
      ->method('toArray')
      ->willReturn(array('demo_key' => 'demo_value'))
    ;
  }

  /**
   * 
   */
  public function testSend() {
    $this->_stubCurl
      ->method('getResponseCode')
      ->willReturn(200)
    ;

    $this->_stubCurl
      ->expects($this->once())
      ->method('post')
      ->with($this->equalTo('json_request_string'))
      ->willReturn('json_response_string')
    ;

    $validateCallback = function($inputArg) {
      $expectedIocParams = array(
        'config' => array(
          'demo_key' => 'demo_value',
          'url' => 'url',
	  'http_headers' => array(
	    'Content-type: application/json;charset=utf-8',
	    'Accept: application/json',
	    'Accept-Encoding: gzip',
	    'Connection: close',
	    'requestreference: request_reference',
	  ),
	  'curl_options' => array(
            CURLOPT_ENCODING => 'gzip',
          ),
	),
      );
      
      $log = $inputArg['log'];
      unset($inputArg['log']);

      // Note - These two lines are too brittle.  Relies on order of headers/array keys and will require updating every release (because of the framework version).
      $versionInfoHeader = array_pop($inputArg['config']['http_headers']);
      $userAgent = array_pop($inputArg['config']);
      
      return (
        $inputArg === $expectedIocParams
	&& $log === 'stub_log'
        && preg_match('/^VERSIONINFO: PHP::.+::1.0.0::.+$/', $versionInfoHeader)
        && preg_match('/^PHP-.+$/', $userAgent)
      );
    };
    
    $this->_stubIoc
      ->method('get')
      ->with($this->equalTo('\Securetrading\Http\Curl'), $this->callback($validateCallback))
      ->willReturn($this->_stubCurl)
    ;

    $this->_stubIoc
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn('stub_log')
    ;

    $returnValue = $this->_http->send('json_request_string', 'request_reference', 'url');
    
    $this->assertEquals('json_response_string', $returnValue);
  }

  /**
   * @expectedException \Securetrading\Stpp\JsonInterface\HttpException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\HttpException::CODE_CURL_ERROR
   */
  public function testSend_When401SendingThrewException() {
    $this->_stubIoc
      ->method('get')
      ->willReturn($this->_stubCurl)
    ;

    $this->_stubCurl
      ->method('post')
      ->will($this->throwException(new \Securetrading\Http\CurlException('Message.')))
    ;

    $this->_http->send('json_request_string', 'request_reference', 'url');
  }

  /**
   * @expectedException \Securetrading\Stpp\JsonInterface\HttpException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\HttpException::CODE_401_INVALID_HTTP_STATUS
   */
  public function testSend_When401HttpStatusReturned() {
    $this->_stubIoc
      ->method('get')
      ->willReturn($this->_stubCurl)
    ;

    $this->_stubCurl
      ->method('getResponseCode')
      ->willReturn(401)
    ;

    $this->_http->send('json_request_string', 'request_reference', 'url');
  }

  /**
   * @expectedException \Securetrading\Stpp\JsonInterface\HttpException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\HttpException::CODE_GENERIC_INVALID_HTTP_STATUS
   */
  public function testSend_WhenOtherInvalidHttpStatusReturned() {
    $this->_stubIoc
      ->method('get')
      ->willReturn($this->_stubCurl)
    ;

    $this->_stubCurl
      ->method('getResponseCode')
      ->willReturn(402)
    ;

    $this->_http->send('json_request_string', 'request_reference', 'url');
  }
}