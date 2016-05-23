<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

class AbstractRequestTest extends \Securetrading\Unittest\UnittestAbstract {
  const REQUEST_REFERENCE_REGEXP = '/A[0123456789abcdefghjkmnpqrtuvwxy]{8}/';

  private $_abstractRequest;

  public function setUp() {
    $iocMock = $this->getMockForAbstractClass('\Securetrading\Ioc\IocInterface');
    $iocMock
      ->expects($this->any())
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($this->getMock('\Securetrading\Stpp\JsonInterface\Log'))
    ;

    $this->_abstractRequest = $this->getMockForAbstractClass('\Securetrading\Stpp\JsonInterface\AbstractRequest', array($iocMock));
  }

  /**
   * 
   */
  public function test_Constructor() {
    $this->assertRegExp(self::REQUEST_REFERENCE_REGEXP, $this->_abstractRequest->getSingle('requestreference'));
  }

  /**
   * 
   */
  public function test_generateRandomRequestReference() {
    for ($i = 0; $i < 1000; $i++) {
      $this->assertRegExp(self::REQUEST_REFERENCE_REGEXP, $this->_($this->_abstractRequest, '_generateRandomRequestReference'));
    }
  }
}
