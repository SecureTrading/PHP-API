<?php

namespace Securetrading\Stpp\JsonInterface;

class Http {
  protected $_httpHeaders = array(
    'Content-type: application/json;charset=utf-8',
    'Accept: application/json',
    'Accept-Encoding: gzip',
    'Connection: close',
  );

  protected $_curl;

  public function __construct(\Securetrading\Http\Curl $http) {
    $this->_curl = $http;
  }

  public function send($jsonRequestString, $requestReference, $url) {
    $this->_httpHeaders[] = 'requestreference: ' . $requestReference;
    $this->_httpHeaders[] = sprintf('VERSIONINFO: PHP::%s::%s::%s', PHP_VERSION, Main::FULL_VERSION, php_uname());
    
    $this->_curl->setUrl($url);
    $this->_curl->setRequestHeaders($this->_httpHeaders);
    $this->_curl->setUserAgent(sprintf('PHP-%s', PHP_VERSION));
    
    try {
      $jsonResponseString = $this->_curl->post($jsonRequestString);
    }
    catch (\Securetrading\Http\CurlException $e) {
      throw new HttpException(sprintf("Error when performing the HTTP request: '%s'.", $e->getMessage()), HttpException::CODE_CURL_ERROR, $e);
    }
    
    if (($httpResponseCode = $this->_curl->getResponseCode()) !== 200) {
      $code = HttpException::CODE_GENERIC_INVALID_HTTP_STATUS;
      $message = sprintf('Unexpected HTTP response code: %s.', $httpResponseCode);
      if ($httpResponseCode === 401) {
	$code = HttpException::CODE_401_INVALID_HTTP_STATUS;
      }
      throw new HttpException($message, $code);
    }

    return $jsonResponseString;
  }
}