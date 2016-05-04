<?php

namespace Securetrading\Stpp\JsonInterface;

class JsonFileReaderException extends \Securetrading\Exception {
  const CODE_FILE_NOT_READ = 1;
  const CODE_FILE_JSON_ERROR = 2;
}