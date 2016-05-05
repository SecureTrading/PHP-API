<?php

namespace Securetrading\Stpp\JsonInterface;

class Main {
  const FULL_VERSION = "1.0.0";

  public static function bootstrapIoc() {
    $ioc = \Securetrading\Ioc\Helper::instance()
      ->addVendorDirs(\Securetrading\Loader\Loader::getRootPath())
      ->addEtcDirs(realpath(__DIR__ . '/../etc'))
      ->loadPackage('stStppJson')
      ->getIoc();
    
    return $ioc;
  }

  public static function bootstrap(array $configData) {
    $ioc = self::bootstrapIoc();
    return $ioc->create('jsonApi', $configData);
  }
}