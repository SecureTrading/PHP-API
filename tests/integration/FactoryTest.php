<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Integration;

class FactoryTest extends \Securetrading\Unittest\IntegrationtestAbstract {
  public function testApiAlias() {
    $ioc = \Securetrading\Stpp\JsonInterface\Main::bootstrapIoc();
    $api = $ioc->get('jsonApi');
    $this->assertInstanceOf('\Securetrading\Stpp\JsonInterface\Api', $api);
  }

  public function testRequestAlias() {
    $ioc = \Securetrading\Stpp\JsonInterface\Main::bootstrapIoc();
    $request = $ioc->get('jsonRequest', array('key' => 'value'));
    $this->assertInstanceOf('\Securetrading\Stpp\JsonInterface\Request', $request);
    $this->assertEquals('value', $request->get('key'));
  }

  public function testRequestsAlias() {
    $ioc = \Securetrading\Stpp\JsonInterface\Main::bootstrapIoc();
    $requests = $ioc->get('jsonRequests');
    $this->assertInstanceOf('\Securetrading\Stpp\JsonInterface\Requests', $requests);
  }
}