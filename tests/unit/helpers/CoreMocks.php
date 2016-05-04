<?php

namespace Securetrading\Stpp\JsonInterface;

function iconv($inputCharset, $outputCharset, $string) {
  return \Securetrading\Unittest\CoreMocker::runCoreMock('iconv', array($inputCharset, $outputCharset, $string));
}