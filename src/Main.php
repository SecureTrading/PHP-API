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
    
    if (array_key_exists('log_filename', $configData)) {
      $ioc->setOption('stpp_json_log_filename', $configData['log_filename']);
    }

    if (array_key_exists('log_filepath', $configData)) {
      $ioc->setOption('stpp_json_log_filepath', $configData['log_filepath']);
    }

    if (array_key_exists('log_archive_filepath', $configData)) {
      $ioc->setOption('stpp_json_log_archive_filepath', $configData['log_archive_filepath']);
    }

    if (array_key_exists('log_level', $configData)) {
      $ioc->setOption('stpp_json_log_level', $configData['log_level']);
    }
    
    return $ioc->create('jsonApi', $configData);
  }
}