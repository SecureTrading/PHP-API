<?php

namespace Securetrading\Stpp\JsonInterface;

abstract class AbstractRequest extends \Securetrading\Data\Data {
  public function __construct(\Securetrading\Ioc\IocInterface $ioc) {
    $requestReference = $this->_generateRandomRequestReference();
    $this->setSingle('requestreference', $requestReference);
    $ioc->getSingleton('\Securetrading\Stpp\JsonInterface\Log')->setRequestReference($requestReference);
  }

  protected function _generateRandomRequestReference() {
    $randomChars = array_rand(
      array_flip(
        str_split('0123456789abcdefghjkmnpqrtuvwxy')
      ),
      8
    );
    return 'A' . implode($randomChars, '');
  }
}