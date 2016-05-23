<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

class AbstractDataTest extends \Securetrading\Unittest\UnittestAbstract {
  private $_logStub;

  private $_abstractData;

  public function setUp() {
    $this->_logStub = $this->getMock('\Securetrading\Stpp\JsonInterface\Log');

    $iocMock = $this->getMock('\Securetrading\Ioc\IocInterface');
    $iocMock
      ->expects($this->once())
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($this->_logStub)
    ;

    $this->_abstractData = $this->getMockForAbstractClass('\Securetrading\Stpp\JsonInterface\AbstractData', array($iocMock));
  }

  public function test_set() {
    $this->_logStub
      ->expects($this->once())
      ->method('debug')
      ->with($this->equalTo('Setting thiskey.'))
    ;

    $this->_abstractData->setSingle('thiskey', 'value');
  }
}