<?php

namespace Securetrading\Stpp\JsonInterface;

class ExceptionMapper {
  const CODE_DEFAULT = 9; # Unknown error.

  protected $_errorCode = self::CODE_DEFAULT;

  protected $_errorData = array();

  public function getOutputErrorCodeAndData(\Exception $e) {    
    switch($e) {
    case ($e instanceof \Securetrading\Stpp\JsonInterface\RequestsException):
      $this->_mapRequestsException($e);
      break;
    case ($e instanceof \Securetrading\Stpp\JsonInterface\ApiException):
      $this->_mapApiException($e);
      break;
    case ($e instanceof \Securetrading\Stpp\JsonInterface\ConverterException):
      $this->_mapConverterException($e);
      break;
    case($e instanceof \Securetrading\Stpp\JsonInterface\HttpException):
      $this->_mapHttpException($e);
      break;
    default:
      $this->_errorData = array($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString(), (string) $e->getPrevious());
    }

    return array($this->_errorCode, $this->_errorData);
  }

  protected function _mapRequestsException(\Securetrading\Stpp\JsonInterface\RequestsException $e) {
    if ($e->getCode() === \Securetrading\Stpp\JsonInterface\RequestsException::CODE_INDIVIDUAL_REQUEST_HAS_DATACENTERURL) {
      $this->_errorCode = 10;
      $this->_errorData = array($e->getMessage());
    }
  }

  protected function _mapApiException(\Securetrading\Stpp\JsonInterface\ApiException $e) {
    if ($e->getCode() === \Securetrading\Stpp\JsonInterface\ApiException::CODE_INVALID_REQUEST_TYPE) {
      $this->_errorCode = 10;
      $this->_errorData = array($e->getMessage());
    }
    else if ($e->getCode() === \Securetrading\Stpp\JsonInterface\ApiException::CODE_MISMATCHING_REQUEST_REFERENCE) {
      $this->_errorCode = 9;
      $this->_errorData = array($e->getMessage());
    }
  }

  protected function _mapConverterException(\Securetrading\Stpp\JsonInterface\ConverterException $e) {
    if ($e->getCode() === \Securetrading\Stpp\JsonInterface\ConverterException::CODE_ENCODE_INVALID_REQUEST_TYPE) {
      $this->_errorCode = 10;
      $this->_errorData = array($e->getMessage());
    }
    else if ($e->getCode() === \Securetrading\Stpp\JsonInterface\ConverterException::CODE_ENCODE_TO_JSON_FAILED) {
      $this->_errorCode = 9;
      $this->_errorData = array($e->getMessage());
    }
    else if ($e->getCode() === \Securetrading\Stpp\JsonInterface\ConverterException::CODE_DECODE_FROM_JSON_FAILED) {
      $this->_errorCode = 9;
      $this->_errorData = array($e->getMessage());
    }
  }

  protected function _mapHttpException(\Securetrading\Stpp\JsonInterface\HttpException $e) {
    if ($e->getCode() === \Securetrading\Stpp\JsonInterface\HttpException::CODE_CURL_ERROR) {
      $this->_errorCode = 8;
      $this->_errorData = array($e->getMessage());
    }
    else if ($e->getCode() === \Securetrading\Stpp\JsonInterface\HttpException::CODE_GENERIC_INVALID_HTTP_STATUS) {
      $this->_errorCode = 8;
      $this->_errorData = array($e->getMessage());
    }
    else if ($e->getCode() === \Securetrading\Stpp\JsonInterface\HttpException::CODE_401_INVALID_HTTP_STATUS) {
      $this->_errorCode = 6;
      $this->_errorData = array($e->getMessage());
    }
  }
}