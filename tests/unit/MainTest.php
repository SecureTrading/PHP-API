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