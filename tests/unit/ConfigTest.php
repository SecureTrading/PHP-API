<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

class ConfigTest extends \Securetrading\Unittest\UnittestAbstract {

  protected function _newInstance($configData = array()) {
    return new \Securetrading\Stpp\JsonInterface\Config($configData);
  }

  /**
   *
   */
  public function testGet() {
    $returnValue = $this->_newInstance(array('k' => 'v'))->get('k');
    $this->assertEquals('v', $returnValue);
  }

  /**
   * @expectedException \Securetrading\Stpp\JsonInterface\ConfigException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\ConfigException::CODE_KEY_NOT_FOUND
   */
  public function testGet_WhenKeyNotSet() {
    $this->_newInstance(array('k' => 'v'))->get('k2');
  }

  /**
   *
   */
  public function testToArray_WithDefaults() {
    $expectedReturnValue = array(
      'datacenterurl' => 'https://webservices.securetrading.net',
      'jsonversion' => '1.00',
      'locale' => 'en_GB',
    );
    $actualReturnValue = $this->_newInstance()->toArray();
    $this->assertEquals($expectedReturnValue, $actualReturnValue);
  }

  /**
   *
   */
  public function testToArray_WithOverriddenConfig() {
    $dataToSet = array(
      'locale' => 'fr_FR',
      'new_key' => 'new_value',
    );

    $expectedReturnValue = array(
      'datacenterurl' => 'https://webservices.securetrading.net',
      'jsonversion' => '1.00',
      'locale' => 'fr_FR',
      'new_key' => 'new_value',
    );

    $actualReturnValue = $this->_newInstance($dataToSet)->toArray();

    $this->assertEquals($expectedReturnValue, $actualReturnValue);
  }
}