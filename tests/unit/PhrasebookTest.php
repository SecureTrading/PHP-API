<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

use org\bovigo\vfs\vfsStream;

class PhrasebookTest extends \Securetrading\Unittest\UnittestAbstract {
  public function setUp() {
    $this->_configStub = $this->getMockForAbstractClass('\Securetrading\Config\ConfigInterface');
  }

  /**
   * @dataProvider providerLookup
   */
  public function testLookup($englishMessage, $locale, $expectedReturnValue) {
    $phrasebookMessages = array(
      'English message in phrasebook.' => array(
        'fr_FR' => 'French message.',
      ),
    );
    $phrasebook = new \Securetrading\Stpp\JsonInterface\Phrasebook($this->_configStub, $phrasebookMessages);

    $actualReturnValue = $phrasebook->lookup($englishMessage, $locale);
    $this->assertEquals($expectedReturnValue, $actualReturnValue);
  }

  public function providerLookup() {
    $this->_addDataSet('English message in phrasebook.', 'fr_FR', 'French message.');
    $this->_addDataSet('English message in phrasebook.', 'gr_GR', 'English message in phrasebook.');
    $this->_addDataSet('English message that does not exist in phrasebook.', 'es_ES', 'English message that does not exist in phrasebook.');
    return $this->_getDataSets();
  }
}