<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

use Securetrading\Stpp\JsonInterface\ExceptionMapper;

class ExceptionMapperTest extends \Securetrading\Unittest\UnittestAbstract {
  
  private $_translatorStub;

  private $_logStub;

  private $_exceptionMapper;

  public function setUp() {
    $this->_translatorStub =  $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Translator')->disableOriginalConstructor()->getMock();
    $this->_logStub = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
    $this->_exceptionMapper = new ExceptionMapper($this->_translatorStub, $this->_logStub);
  }

  /**
   * @dataProvider providerGetOutputErrorMessage
   */
  public function testGetOutputErrorMessage(\Exception $e, $expectedCode, $expectedErrorData) {
    $this->_translatorStub
      ->expects($this->once())
      ->method('translate')
      ->with($this->equalTo($expectedCode))
      ->willReturn('Translated message.')
    ;

    $returnValue = $this->_exceptionMapper->getOutputErrorMessage($e);

    $returnCode = $returnValue[0];
    $returnMessage = $returnValue[1];
    $returnData = $returnValue[2];

    $this->assertEquals($expectedCode, $returnCode);
    $this->assertEquals('Translated message.', $returnMessage);
    $this->assertEquals($expectedErrorData, $returnData);
  }

  public function providerGetOutputErrorMessage() {    
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

    // \Exception (or any other unhandled exception type):

    $e = new \Exception('Message.');
    $this->_addDataSet($e, \Securetrading\Stpp\JsonInterface\ExceptionMapper::CODE_DEFAULT, array());

    return $this->_getDataSets();
  }

  /**
   *
   */
  public function testGetOutputErrorMessage_WhenTranslatorThrowsException() {
    $e = new \Securetrading\Stpp\JsonInterface\HttpException('Message.', \Securetrading\Stpp\JsonInterface\HttpException::CODE_GENERIC_INVALID_HTTP_STATUS);

    $exception = new \Exception('Exception message.');

    $this->_translatorStub
      ->expects($this->once())
      ->method('translate')
      ->with($this->equalTo(8))
      ->will($this->throwException($exception))
    ;

    $this->_logStub
      ->expects($this->exactly(2))
      ->method('alert')
      ->withConsecutive(
        array($this->equalTo('Could not translate the following exception.')), 
	array($this->identicalTo($exception))
      )
    ;

    $returnValue = $this->_exceptionMapper->getOutputErrorMessage($e);

    $returnCode = $returnValue[0];
    $returnMessage = $returnValue[1];
    $returnData = $returnValue[2];

    $this->assertEquals(\Securetrading\Stpp\JsonInterface\ExceptionMapper::CODE_COULD_NOT_TRANSLATE, $returnCode);
    $this->assertEquals('Could not translate message with code "8".', $returnMessage);
    $this->assertEquals(array(), $returnData);
  }
}
