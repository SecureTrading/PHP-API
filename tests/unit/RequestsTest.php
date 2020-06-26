<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

class RequestsTest extends \Securetrading\Unittest\UnittestAbstract {
  private $_requests;

  private function _getStubRequest() {
    return $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Request')->disableOriginalConstructor()->getMock();
  }

  public function setUp() : void {
    $iocMock = $this->getMockForAbstractClass('\Securetrading\Ioc\IocInterface');
    $iocMock
      ->expects($this->any())
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($this->createMock('\Securetrading\Stpp\JsonInterface\Log'))
    ;

    $this->_requests = new \Securetrading\Stpp\JsonInterface\Requests($iocMock);
  }

  /**
   * 
   */
  public function testAddRequest() {
    $returnValue = $this->_requests->addRequest($this->_getStubRequest());
    $this->assertSame($this->_requests, $returnValue);
  }

  /**
   * 
   */
  public function testAddRequest_WithDataCenterUrl() {
    $this->expectException(\Securetrading\Stpp\JsonInterface\RequestsException::class);
    $this->expectExceptionCode(\Securetrading\Stpp\JsonInterface\RequestsException::CODE_INDIVIDUAL_REQUEST_HAS_DATACENTERURL);
    $requestStub = $this->_getStubRequest();
    $requestStub->method('hasSingle')->with($this->equalTo('datacenterurl'))->willReturn(true);
    $returnValue = $this->_requests->addRequest($requestStub);
  }

  /**
   * 
   */
  public function testGetRequests() {
    $requestStub1 = $this->_getStubRequest();
    $requestStub2 = $this->_getStubRequest();
    $this->_requests->addRequest($requestStub1);
    $this->_requests->addRequest($requestStub2);
    $this->assertSame(array($requestStub1, $requestStub2), $this->_requests->getRequests());
  }
}
