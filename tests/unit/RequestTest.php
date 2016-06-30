<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

class RequestTest extends \Securetrading\Unittest\UnittestAbstract {
  const REQUEST_REFERENCE_REGEXP = '/A[0123456789abcdefghjkmnpqrtuvwxy]{8}/';

  private $_request;

  public function setUp() {
    $iocMock = $this->getMockForAbstractClass('\Securetrading\Ioc\IocInterface');
    $iocMock
      ->expects($this->any())
      ->method('getSingleton')
      ->with($this->equalTo('\Securetrading\Stpp\JsonInterface\Log'))
      ->willReturn($this->getMock('\Securetrading\Stpp\JsonInterface\Log'))
    ;

    $this->_request = new \Securetrading\Stpp\JsonInterface\Request($iocMock);
  }

  /**
   * @dataProvider provider_setCacheToken
   */
  public function test_setCacheToken($inputCacheToken, $expectedReturnValue, $assertMessage) {
    $this->_request->setSingle('cachetoken', $inputCacheToken);
    $this->_request->uns('requestreference'); // Randomly generated.
    $this->assertSame($expectedReturnValue, $this->_request->getAll(), $assertMessage);
  }

  public function provider_setCacheToken() {
    $this->_addDataSet(
      "eyJkYXRhY2VudGVydXJsIjogImh0dHBzOi8vd2Vic2VydmljZXMuc2VjdXJldHJhZGluZy5uZXQiLCAiY2FjaGV0b2tlbiI6ICIxNy1hZTdlNTExMTcyYTA3YzJmYjQ1ZGI0YzczMzg4MDg3ZTRkODUwNzc3Mzg2YTVkNzIwMjlhYWY4OTU4N2YzY2YwIn0=",
      array(
        "datacenterurl" => "https://webservices.securetrading.net",
	"cachetoken" => "17-ae7e511172a07c2fb45db4c73388087e4d850777386a5d72029aaf89587f3cf0",
      ),
      "Valid data."
    );
    $this->_addDataSet(
      "17-6a0287dd04497ba8dab257acbd983741f55410b5c7094637d8c3f0fb57bd25ec",
      array(
        "cachetoken" => "17-6a0287dd04497ba8dab257acbd983741f55410b5c7094637d8c3f0fb57bd25ec",
      ),
      "A literal cache token - is not base64 decoded into a valid stringified JSON object."
    );
    $this->_addDataSet(
      "eyJkYXRhY2VudGVydXJsIjogImh0dHBzOi8vd2Vic2VydmljZXMuc2VjdXJldHJhZGluZy5uZXQiLCAiY2FjaGV0b2tlbiI6ICIxNy1hZTdlNTExMTcy",
      array(
	"cachetoken" => "eyJkYXRhY2VudGVydXJsIjogImh0dHBzOi8vd2Vic2VydmljZXMuc2VjdXJldHJhZGluZy5uZXQiLCAiY2FjaGV0b2tlbiI6ICIxNy1hZTdlNTExMTcy",
      ),
      "An incorrectly stringified JSON object; it is missing the closing brace (the base 64 decoding is OK)."
    );
    return $this->_getDataSets();
  }
}
