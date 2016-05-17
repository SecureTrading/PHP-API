<?php

namespace Securetrading\Stpp\JsonInterface;

class Phrasebook
{
  protected $_config;

  protected $_phraseBook = array();

  public function __construct(ConfigInterface $config, array $phraseBook) {
    $this->_config = $config;
    $this->_phraseBook = $phraseBook;
  }

  public function lookup($englishMessage, $locale = null) {
    if (!$locale) {
      $locale = $this->_config->get('locale');
    }

    $translatedMessage = $englishMessage;

    if (array_key_exists($englishMessage, $this->_phraseBook) && array_key_exists($locale, $this->_phraseBook[$englishMessage])) {
      $translatedMessage = $this->_phraseBook[$englishMessage][$locale];
    }

    return $translatedMessage;
  }
}