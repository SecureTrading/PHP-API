<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

use org\bovigo\vfs\vfsStream;

class TranslatorTest extends \Securetrading\Unittest\UnittestAbstract {
  private $_phrasebookStub;

  private $_logStub;

  public function setUp() {
    $this->_phrasebookStub = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Phrasebook')->disableOriginalConstructor()->getMock();
    $this->_logStub = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
  }

  private function _newInstance(array $messages) {
    return new \Securetrading\Stpp\JsonInterface\Translator($messages, $this->_phrasebookStub, $this->_logStub);
  }

  /**
   * 
   */
  public function testTranslate_WhenCodeNotInMessages() {
    $this->_logStub
      ->expects($this->once())
      ->method('alert')
      ->with($this->equalTo('There was no error message mapping for code \'code_that_does_not_exist\'.'))
    ;

    $translator = $this->_newInstance(array());
    $actualReturnValue = $translator->translate('code_that_does_not_exist', 'default_message');
    $this->assertEquals('default_message', $actualReturnValue);
  }

  /**
   *
   */
  public function testTranslate_WhenCodeInMessages() {
    $this->_phrasebookStub
      ->expects($this->once())
      ->method('lookup')
      ->with($this->equalTo('English message.'), $this->equalTo('locale'))
      ->willReturn('Translated message.')
    ;

    $translator = $this->_newInstance(array('code1' => 'English message.'));
    $actualReturnValue = $translator->translate('code1', 'default_message', 'locale');
    $this->assertEquals('Translated message.', $actualReturnValue);
  }

  /**
   *
   */
  public function testTranslate_WhenCodeInMessages_AndLocaleNotSet() {
    $this->_phrasebookStub
      ->expects($this->once())
      ->method('lookup')
      ->with($this->equalTo('English message.'), $this->equalTo(null))
      ->willReturn('Translated message.')
    ;

    $translator = $this->_newInstance(array('code1' => 'English message.'));
    $actualReturnValue = $translator->translate('code1', 'default_message');
    $this->assertEquals('Translated message.', $actualReturnValue);
  }
}