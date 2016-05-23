<?php

namespace Securetrading\Stpp\JsonInterface;

abstract class AbstractData extends \Securetrading\Data\Data {
  protected $_ioc;

  public function __construct(\Securetrading\Ioc\IocInterface $ioc) {
    $this->_log = $ioc->getSingleton('\Securetrading\Stpp\JsonInterface\Log');
  }

  protected function _set($key, $value) {
    $this->_log->debug(sprintf('Setting %s.', $key));
    parent::_set($key, $value);
  }
}