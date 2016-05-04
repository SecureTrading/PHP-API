<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

class HttpTest extends \Securetrading\Unittest\UnittestAbstract {

  private $_http;

  private $_stubCurl;

  public function setUp() {
    $this->_stubCurl = $this->getMock('\Securetrading\Http\Curl', array(), array($this->getMockForAbstractClass('\Psr\Log\LoggerInterface')));
    $this->_http = new \Securetrading\Stpp\JsonInterface\Http($this->_stubCurl);
  }

  /**
   * 
   */
  public function testSend() {
    $validateCallback = function($inputArg) {
      $headersArray = array(
        'Content-type: application/json;charset=utf-8',
	'Accept: application/json',
	'Accept-Encoding: gzip',
	'Connection: close',
	'requestreference: request_reference',
      );

      $versionInfo = array_pop($inputArg); // Note - Too brittle.  This assumes the VERSIONINFO is the last header added by the SUT.

      return ($inputArg === $headersArray) && preg_match('/^VERSIONINFO: PHP::.+::1.0.0::.+$/', $versionInfo); // Note - Too brittle.  Will have to update the library version here every release.
    };

    $this->_stubCurl
      ->method('getResponseCode')
      ->willReturn(200)
    ;

    $this->_stubCurl
      ->expects($this->once())
      ->method('setUrl')
      ->with($this->equalTo('url'))
    ;

    $this->_stubCurl
      ->expects($this->once())
      ->method('setRequestHeaders')
      ->with($this->callback($validateCallback))
    ;

    $that = $this;

    $this->_stubCurl
      ->expects($this->once())
      ->method('setUserAgent')
      ->with($this->callback(function($inputUserAgent) use ($that) {
        return preg_match('/^PHP-.+$/', $inputUserAgent);
      }))
    ;

    $this->_stubCurl
      ->expects($this->once())
      ->method('post')
      ->with($this->equalTo('json_request_string'))
      ->willReturn('json_response_string')
    ;

    $returnValue = $this->_http->send('json_request_string', 'request_reference', 'url');
    
    $this->assertEquals('json_response_string', $returnValue);
  }

  /**
   * @expectedException \Securetrading\Stpp\JsonInterface\HttpException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\HttpException::CODE_CURL_ERROR
   */
  public function testSend_When401SendingThrewException() {
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
    $this->_stubCurl
      ->method('getResponseCode')
      ->willReturn(402)
    ;

    $this->_http->send('json_request_string', 'request_reference', 'url');
  }
}