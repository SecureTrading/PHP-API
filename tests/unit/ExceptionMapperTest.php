<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

use Securetrading\Stpp\JsonInterface\ExceptionMapper;

class ExceptionMapperTest extends \Securetrading\Unittest\UnittestAbstract {
  
  private $_translatorStub;

  private $_logStub;

  private $_exceptionMapper;

  public function setUp() {
    $this->_exceptionMapper = new ExceptionMapper();
  }

  /**
   * @dataProvider providerGetOutputErrorCodeAndData
   */
  public function testGetOutputErrorCodeAndData(\Exception $e, $expectedCode, $expectedErrorData) {
    $returnValue = $this->_exceptionMapper->getOutputErrorCodeAndData($e);

    $returnCode = $returnValue[0];
    $returnData = $returnValue[1];

    $this->assertEquals($expectedCode, $returnCode);
    $this->assertEquals($expectedErrorData, $returnData);
  }

  public function providerGetOutputErrorCodeAndData() {    
    // \Securetrading\Stpp\JsonInterface\RequestsException:

    $e = new \Securetrading\Stpp\JsonInterface\RequestsException('Message.', \Securetrading\Stpp\JsonInterface\RequestsException::CODE_INDIVIDUAL_REQUEST_HAS_DATACENTERURL);
    $this->_addDataSet($e, 10, array('Message.'));

    $e = new \Securetrading\Stpp\JsonInterface\RequestsException('Message.', 999);
    $this->_addDataSet($e, \Securetrading\Stpp\JsonInterface\ExceptionMapper::CODE_DEFAULT, array());

    // \Securetrading\Stpp\JsonInterface\ApiException:

    $e = new \Securetrading\Stpp\JsonInterface\ApiException('Message.', \Securetrading\Stpp\JsonInterface\ApiException::CODE_INVALID_REQUEST_TYPE);
    $this->_addDataSet($e, 10, array('Message.'));

    $e = new \Securetrading\Stpp\JsonInterface\ApiException('Message.', \Securetrading\Stpp\JsonInterface\ApiException::CODE_MISMATCHING_REQUEST_REFERENCE);
    $this->_addDataSet($e, 9, array('Message.'));

    $e = new \Securetrading\Stpp\JsonInterface\ApiException('Message.', 999);
    $this->_addDataSet($e, \Securetrading\Stpp\JsonInterface\ExceptionMapper::CODE_DEFAULT, array());

    // \Securetrading\Stpp\JsonInterface\ConverterException:

    $e = new \Securetrading\Stpp\JsonInterface\ConverterException('Message.', \Securetrading\Stpp\JsonInterface\ConverterException::CODE_ENCODE_INVALID_REQUEST_TYPE);
    $this->_addDataSet($e, 10, array('Message.'));

    $e = new \Securetrading\Stpp\JsonInterface\ConverterException('Message.', \Securetrading\Stpp\JsonInterface\ConverterException::CODE_ENCODE_TO_JSON_FAILED);
    $this->_addDataSet($e, 9, array('Message.'));

    $e = new \Securetrading\Stpp\JsonInterface\ConverterException('Message.', \Securetrading\Stpp\JsonInterface\ConverterException::CODE_DECODE_FROM_JSON_FAILED);
    $this->_addDataSet($e, 9, array('Message.'));

    $e = new \Securetrading\Stpp\JsonInterface\ConverterException('Message.', 999);
    $this->_addDataSet($e, \Securetrading\Stpp\JsonInterface\ExceptionMapper::CODE_DEFAULT, array());

    // \Securetrading\Stpp\JsonInterface\HttpException:
    
    $e = new \Securetrading\Stpp\JsonInterface\HttpException('Message.', \Securetrading\Stpp\JsonInterface\HttpException::CODE_CURL_ERROR);
    $this->_addDataSet($e, 8, array('Message.'));

    $e = new \Securetrading\Stpp\JsonInterface\HttpException('Message.', \Securetrading\Stpp\JsonInterface\HttpException::CODE_GENERIC_INVALID_HTTP_STATUS);
    $this->_addDataSet($e, 8, array('Message.'));

    $e = new \Securetrading\Stpp\JsonInterface\HttpException('Message.', \Securetrading\Stpp\JsonInterface\HttpException::CODE_401_INVALID_HTTP_STATUS);
    $this->_addDataSet($e, 6, array('Message.'));

    $e = new \Securetrading\Stpp\JsonInterface\HttpException('Message.', 999);
    $this->_addDataSet($e, \Securetrading\Stpp\JsonInterface\ExceptionMapper::CODE_DEFAULT, array());

    return $this->_getDataSets();
  }

  /**
   * 
   */
  public function testGetOutputErrorCodeAndData_WithUnhanldedException() {
    $previousException = new \Exception('Previous exception message.');
    $exception = new \Exception('Message.', 0, $previousException);

    $returnValue = $this->_exceptionMapper->getOutputErrorCodeAndData($exception);

    $returnedErrorCode = $returnValue[0];
    $returnedErrorData = $returnValue[1];

    $this->assertEquals(\Securetrading\Stpp\JsonInterface\ExceptionMapper::CODE_DEFAULT, $returnedErrorCode);

    $this->assertEquals('Message.', $returnedErrorData[0]); # Exception message.
    $this->assertRegExp("/^.+ExceptionMapperTest\.php$/", $returnedErrorData[1]); # Exception file.
    $this->assertTrue(is_int($returnedErrorData[2])); # Exception line number.
    $this->assertRegExp("/^#0 \[internal function\].+$/m", $returnedErrorData[3]); # Exception stack trace.
    $this->assertRegExp("/^.+Previous exception message\..+$/m", $returnedErrorData[4]); # Previous exception stack trace.
  }
}
