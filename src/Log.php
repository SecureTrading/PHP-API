<?php

namespace Securetrading\Stpp\JsonInterface;

class Log extends \Psr\Log\AbstractLogger {
  protected $_logger;

  protected $_requestReference;

  public function setLogger(\Psr\Log\LoggerInterface $logger) {
    $this->_logger = $logger;
    return $this;
  }

  protected function _getLogger() {
    if ($this->_logger === null) {
      throw new LogException('The logger has not been set.', LogException::CODE_LOGGER_NOT_SET);
    }
    return $this->_logger;
  }

  public function setRequestReference($requestReference) {
    $this->_requestReference = $requestReference;
    return $this;
  }

  public function log($logLevel, $message, array $context = array()) {
    $message = $this->_formatMessage($message);
    $this->_getLogger()->log($logLevel, $message, $context);
    return $this;
  }

  protected function _formatMessage($message) {
    $requestReference = $this->_requestReference ? $this->_requestReference : '   NOREF';
    return sprintf('%s - %s', $requestReference, $message);
  }
}