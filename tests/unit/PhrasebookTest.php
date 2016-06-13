<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

use org\bovigo\vfs\vfsStream;

class PhrasebookTest extends \Securetrading\Unittest\UnittestAbstract {
  public function setUp() {
    $this->_configStub = $this->getMock('\Securetrading\Stpp\JsonInterface\Config');
    $this->_phrasebookMessages = array(
      'English message in phrasebook.' => array(
        'fr_FR' => 'French message.',
      ),
    );
  }

  /**
   * @dataProvider providerLookup
   */
  public function testLookup($englishMessage, $locale, $expectedReturnValue) {
    $phrasebook = new \Securetrading\Stpp\JsonInterface\Phrasebook($this->_configStub, $this->_phrasebookMessages);
    $actualReturnValue = $phrasebook->lookup($englishMessage, $locale);
    $this->assertEquals($expectedReturnValue, $actualReturnValue);
  }

  public function providerLookup() {
    $this->_addDataSet('English message in phrasebook.', 'fr_FR', 'French message.');
    $this->_addDataSet('English message in phrasebook.', 'gr_GR', 'English message in phrasebook.');
    $this->_addDataSet('English message that does not exist in phrasebook.', 'es_ES', 'English message that does not exist in phrasebook.');
    return $this->_getDataSets();
  }

  /**
   *
   */
  public function testLookup_WhenNoLocaleGiven() {
    $englishMessage = "English message in phrasebook.";
    $expectedReturnValue = "French message.";

    $this->_configStub
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('locale'))
      ->willReturn('fr_FR')
    ;
    
    $phrasebook = new \Securetrading\Stpp\JsonInterface\Phrasebook($this->_configStub, $this->_phrasebookMessages);
    $actualReturnValue = $phrasebook->lookup($englishMessage);
    $this->assertEquals($expectedReturnValue, $actualReturnValue);
  }
}