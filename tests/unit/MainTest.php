<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

class MainTest extends \Securetrading\Unittest\UnittestAbstract {
  // Note - Main::bootstrapIoc tested by our Api integration tests.

  public function testBootstrap() {
    $configData = array('a' => 'b');
    $api = \Securetrading\Stpp\JsonInterface\Main::bootstrap($configData);

    $reflection = new \ReflectionClass($api);
    $property = $reflection->getProperty('_config');
    $property->setAccessible(true);
    $internalConfig = $property->getValue($api);

    $this->assertEquals('b', $internalConfig->get('a'));
  }

  public function testBootstrap_SetsLoggingOptions() {
    $configData = array(
      'log_filename' => 'our_log_filename',
      'log_filepath' => 'our_log_filepath',
      'log_archive_filepath' => 'our_log_archive_filepath',
      'log_level' => 'our_log_level',
    );
    $api = \Securetrading\Stpp\JsonInterface\Main::bootstrap($configData);

    $reflection = new \ReflectionClass($api);
    $property = $reflection->getProperty('_ioc');
    $property->setAccessible(true);
    $ioc = $property->getValue($api);
    
    $this->assertEquals('our_log_filename', $ioc->getOption('stpp_json_log_filename'));
    $this->assertEquals('our_log_filepath', $ioc->getOption('stpp_json_log_filepath'));
    $this->assertEquals('our_log_archive_filepath', $ioc->getOption('stpp_json_log_archive_filepath'));
    $this->assertEquals('our_log_level', $ioc->getOption('stpp_json_log_level'));
  }

  public function testBootstrap_FromHelperFunction() {
    $configData = array('a' => 'b');
    $api = \Securetrading\api($configData);

    $reflection = new \ReflectionClass($api);
    $property = $reflection->getProperty('_config');
    $property->setAccessible(true);
    $internalConfig = $property->getValue($api);

    $this->assertEquals('b', $internalConfig->get('a'));
  }
}