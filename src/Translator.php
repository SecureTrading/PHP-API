<?php

namespace Securetrading\Stpp\JsonInterface;

class Translator {
  protected $_messages = array();

  protected $_phrasebook;

  public function __construct(array $messages, Phrasebook $phrasebook) {
    $this->_messages = $messages;
    $this->_phrasebook = $phrasebook;
  }

  public function translate($code, $locale = null) {
    
    if (!array_key_exists($code, $this->_messages)) {
      throw new TranslatorException(sprintf("Code '%s' not in messages.", $code), TranslatorException::CODE_NO_MESSAGE_FOR_CODE);
    }

    $englishMessage = $this->_messages[$code];
    
    return $this->_phrasebook->lookup($englishMessage, $locale);
  }
}