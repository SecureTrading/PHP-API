<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

use org\bovigo\vfs\vfsStream;

class TranslatorTest extends \Securetrading\Unittest\UnittestAbstract {
  public function setUp() {
    $this->_phrasebookStub = $this->getMockBuilder('\Securetrading\Stpp\JsonInterface\Phrasebook')->disableOriginalConstructor()->getMock();
  }

  private function _newInstance(array $messages) {
    return new \Securetrading\Stpp\JsonInterface\Translator($messages, $this->_phrasebookStub);
  }

  /**
   * @expectedException \Securetrading\Stpp\JsonInterface\TranslatorException
   * @expectedExceptionCode \Securetrading\Stpp\JsonInterface\TranslatorException::CODE_NO_MESSAGE_FOR_CODE
   */
  public function testTranslate_WhenCodeNotInMessages() {
    $translator = $this->_newInstance(array());
    $translator->translate('code_that_does_not_exist');
  }

  /**
   *
   */
  public function testTranslate_WhenCodeInMessages() {
    $this->_phrasebookStub
      ->expects($this->once())
      ->method('lookup')
      ->with($this->equalTo('English message.'))
      ->willReturn('Translated message.')
    ;

    $translator = $this->_newInstance(array('code1' => 'English message.'));
    $actualReturnValue = $translator->translate('code1');
    $this->assertEquals('Translated message.', $actualReturnValue);
  }
}