<?php

namespace Securetrading\Stpp\JsonInterface\Tests\Unit;

use org\bovigo\vfs\vfsStream;

class JsonFileReaderTest extends \Securetrading\Unittest\UnittestAbstract {
  private $_rootDir;

  public function setUp() : void {
    $this->_rootDir = vfsStream::setup('rootTestDirectory');
    $this->_jsonFileReader = new \Securetrading\Stpp\JsonInterface\JsonFileReader();
  }

  private function _createFile($filename, $contents) {
    $newFile = vfsStream::newFile($filename, 0777);
    $newFile->at($this->_rootDir);
    $newFile->setContent($contents);
    return $newFile;
  }

  /**
   * 
   */
  public function testGetContentsAsArray_WhenFileNotLoaded() {
    $this->expectException(\Securetrading\Stpp\JsonInterface\JsonFileReaderException::class);
    $this->expectExceptionCode(\Securetrading\Stpp\JsonInterface\JsonFileReaderException::CODE_FILE_NOT_READ);
    
    $fileToTest = $this->_createFile('file_to_test.json', '');
    $this->_jsonFileReader->getContentsAsArray($fileToTest->url());
   
  }

  /**
   * 
   */
  public function testGetContentsAsArray_WhenFileContainsInvalidJson() {
    $this->expectException(\Securetrading\Stpp\JsonInterface\JsonFileReaderException::class);
    $this->expectExceptionCode(\Securetrading\Stpp\JsonInterface\JsonFileReaderException::CODE_FILE_JSON_ERROR);
    
    $fileToTest = $this->_createFile('file_to_test.json', '{"abc":"def"');
    $this->_jsonFileReader->getContentsAsArray($fileToTest->url());
  }

  /**
   *
   */
  public function testGetContentsAsArray() {
    $fileToTest = $this->_createFile('file_to_test.json', '{"abc":"def","hij":"klm"}');
    $expectedReturnValue = array(
      'abc' => 'def',
      'hij' => 'klm',
    );
    $actualReturnValue = $this->_jsonFileReader->getContentsAsArray($fileToTest->url());
    $this->assertEquals($expectedReturnValue, $actualReturnValue);
  }
}