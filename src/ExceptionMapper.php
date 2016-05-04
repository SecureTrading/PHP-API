<?php

namespace Securetrading\Stpp\JsonInterface;

class ExceptionMapper {
  const CODE_DEFAULT = 9; # Unknown error.
  const CODE_COULD_NOT_TRANSLATE = 9; # Unknown error.

  protected $_translator;

  protected $_log;

  protected $_errorCode = self::CODE_DEFAULT;

  protected $_errorData = array();

  public function __construct(Translator $translator, \Psr\Log\LoggerInterface $log) {
    $this->_translator = $translator;
    $this->_log = $log;
  }

  public function getOutputErrorMessage(\Exception $e) {    
    switch($e) {
    case ($e instanceof \Securetrading\Stpp\JsonInterface\RequestsException):
      $this->_mapRequestsMessage($e);
      break;
    case ($e instanceof \Securetrading\Stpp\JsonInterface\ApiException):
      $this->_mapApiMessage($e);
      break;
    case ($e instanceof \Securetrading\Stpp\JsonInterface\ConverterException):
      $this->_mapConverterMessage($e);
      break;
    case($e instanceof \Securetrading\Stpp\JsonInterface\HttpException):
      $this->_mapHttpMessage($e);
      break;
    }   
    
    try {
      $translatedMessage = $this->_translator->translate($this->_errorCode);
    }
    catch (\Exception $e) {
      $this->_log->alert('Could not translate the following exception.');
      $this->_log->alert($e);
      return array(self::CODE_COULD_NOT_TRANSLATE, sprintf('Could not translate message with code "%s".', $this->_errorCode), array());
    }
    
    return array($this->_errorCode, $translatedMessage, $this->_errorData);
  }

  protected function _mapRequestsMessage(\Securetrading\Stpp\JsonInterface\RequestsException $e) {
    if ($e->getCode() === \Securetrading\Stpp\JsonInterface\RequestsException::CODE_INDIVIDUAL_REQUEST_HAS_DATACENTERURL) {
      $this->_errorCode = 10;
      $this->_errorData = array($e->getMessage());
    }
  }

  protected function _mapApiMessage(\Securetrading\Stpp\JsonInterface\ApiException $e) {
    if ($e->getCode() === \Securetrading\Stpp\JsonInterface\ApiException::CODE_INVALID_REQUEST_TYPE) {
      $this->_errorCode = 10;
      $this->_errorData = array($e->getMessage());
    }
    else if ($e->getCode() === \Securetrading\Stpp\JsonInterface\ApiException::CODE_MISMATCHING_REQUEST_REFERENCE) {
      $this->_errorCode = 9;
      $this->_errorData = array($e->getMessage());
    }
  }

  protected function _mapConverterMessage(\Securetrading\Stpp\JsonInterface\ConverterException $e) {
    if ($e->getCode() === \Securetrading\Stpp\JsonInterface\ConverterException::CODE_ENCODE_INVALID_REQUEST_TYPE) {
      $this->_errorCode = 10;
      $this->_errorData = array($e->getMessage());
    }
  }

  protected function _mapHttpMessage(\Securetrading\Stpp\JsonInterface\HttpException $e) {
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