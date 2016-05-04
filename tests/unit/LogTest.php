<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

class LogTest extends \Securetrading\Unittest\UnittestAbstract {
  protected $_log;

  public function setUp() {
    $this->_log = new \Securetrading\Stpp\JsonInterface\Log();
  }
  
  protected function getAbstractLoggerMock() {
    return $this->getMockForAbstractClass('\Psr\Log\AbstractLogger');
  }
  
  /**
   *
   */
  public function testSetLogger() {
    $mockLogger = $this->getAbstractLoggerMock();
    $returnValue = $this->_log->setLogger($mockLogger);
    $this->assertSame($this->_log, $returnValue);
  }

  /**
   * @expectedException \Securetrading\Stpp\JsonInterface\LogException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\LogException::CODE_LOGGER_NOT_SET
   */
  public function testGetLogger_IfNotSet() {
    $this->_($this->_log, '_getLogger');
  }

  /**
   * 
   */
  public function testGetLogger_IfSet() {
    $mockLogger = $this->getAbstractLoggerMock();
    $this->_log->setLogger($mockLogger);
    $returnValue = $this->_($this->_log, '_getLogger');
    $this->assertSame($mockLogger, $returnValue);
  }

  public function testSetRequestReference() {
    $returnValue = $this->_log->setRequestReference('A01234567');
    $this->assertSame($this->_log, $returnValue);
  }

  /**
   * @dataProvider providerLog
   */
  public function testLog($requestReference, $logLevel, $message, array $context, $expectedMessagePassedToLogger) {
    $mockLogger = $this->getAbstractLoggerMock();
    $mockLogger
      ->expects($this->once())
      ->method('log')
      ->with(
        $this->equalTo($logLevel),
	$this->equalTo($expectedMessagePassedToLogger),
	$this->equalTo($context)
      )
    ;
    $this->_log->setLogger($mockLogger);
    $this->_log->setRequestReference($requestReference);

    $returnValue = $this->_log->log($logLevel, $message, $context);
    $this->assertSame($this->_log, $returnValue);
  }

  public function providerLog() {
    $this->_addDataSet('A01234567', 'myloglevel', 'my message with {c}.', array('c' => 'context'), 'A01234567 - my message with {c}.');
    return $this->_getDataSets();
  }

  /**
   * @dataProvider provider_formatMessage
   */
  public function test_formatMessage($message, $requestReference, $expectedReturnValue) {
    $this->_log->setRequestReference($requestReference);
    $actualReturnValue = $this->_($this->_log, '_formatMessage', $message);
    $this->assertEquals($expectedReturnValue, $actualReturnValue);
  }

  public function provider_formatMessage() {
    $this->_addDataSet('Message', 'A01234567', 'A01234567 - Message');
    $this->_addDataSet('Message', null, '   NOREF - Message');
    return $this->_getDataSets();
  }
}