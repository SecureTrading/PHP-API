<?php

namespace Securetrading\Stpp\JsonInterface;

class Translator {
  protected $_messages = array();

  protected $_phrasebook;

  protected $_log;

  public function __construct(array $messages, Phrasebook $phrasebook, \Psr\Log\LoggerInterface $log) {
    $this->_messages = $messages;
    $this->_phrasebook = $phrasebook;
    $this->_log = $log;
  }

  public function translate($code, $defaultMessage, $locale = null) {
    if (array_key_exists($code, $this->_messages)) {
      $englishMessage = $this->_messages[$code];
      $translatedMessage = $this->_phrasebook->lookup($englishMessage, $locale);
    }
    else {
      $this->_log->alert(sprintf('There was no error message mapping for code \'%s\'.', $code));
      $translatedMessage = $defaultMessage;
    }
    return $translatedMessage;
  }
}