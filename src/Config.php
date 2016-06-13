<?php

namespace Securetrading\Stpp\JsonInterface;

class Config implements ConfigInterface {
  protected $_data;

  protected $_defaults = array(
    'datacenterurl' => 'https://webservices.securetrading.net',
    'jsonversion' => '1.00',
    'locale' => 'en_GB',
  );

  public function __construct($config = array()) {
    $this->_data = array_replace($this->_defaults, $config);
  }

  public function get($key) {
    if (!array_key_exists($key, $this->_data)) {
      throw new ConfigException(sprintf('Key %s does not exist.', $key), ConfigException::CODE_KEY_NOT_FOUND);
    }
    return $this->_data[$key];
  }

  public function toArray() {
    return $this->_data;
  }
}