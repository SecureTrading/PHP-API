<?php

namespace Securetrading\Stpp\JsonInterface;

class Http {
  protected $_httpHeaders = array(
    'Content-type: application/json;charset=utf-8',
    'Accept: application/json',
    'Accept-Encoding: gzip',
    'Connection: close',
  );

  protected $_ioc;

  protected $_config = array();

  public function __construct(\Securetrading\Ioc\IocInterface $ioc, ConfigInterface $config) {
    $this->_ioc = $ioc;
    $this->_config = $config;
  }

  public function send($jsonRequestString, $requestReference, $url) {
    $this->_httpHeaders[] = 'requestreference: ' . $requestReference;
    $this->_httpHeaders[] = sprintf('VERSIONINFO: PHP::%s::%s::%s', PHP_VERSION, Main::FULL_VERSION, php_uname());

    $config = array_replace($this->_config->toArray(), array(
      'url' => $url,
      'http_headers' => $this->_httpHeaders,
      'curl_options' => array(
        CURLOPT_ENCODING => 'gzip',
      ),
      'user_agent' => sprintf('PHP-%s', PHP_VERSION),
    ));

    $curl = $this->_ioc->get('\Securetrading\Http\Curl', array('log' => $this->_ioc->getSingleton('\Securetrading\Stpp\JsonInterface\Log'), 'config' => $config));

    try {
      $jsonResponseString = $curl->post($jsonRequestString);
    }
    catch (\Securetrading\Http\CurlException $e) {
      throw new HttpException(sprintf("Error when performing the HTTP request: '%s'.", $e->getMessage()), HttpException::CODE_CURL_ERROR, $e);
    }
    
    if (($httpResponseCode = $curl->getResponseCode()) !== 200) {
      $code = HttpException::CODE_GENERIC_INVALID_HTTP_STATUS;
      $message = sprintf('HTTP code %s.', $httpResponseCode);
      if ($httpResponseCode === 401) {
	$code = HttpException::CODE_401_INVALID_HTTP_STATUS;
      }
      throw new HttpException($message, $code);
    }

    return $jsonResponseString;
  }
}