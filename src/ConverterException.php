<?php

namespace Securetrading\Stpp\JsonInterface;

class ConverterException extends \Securetrading\Exception {
  const CODE_ENCODE_INVALID_REQUEST_TYPE = 1;
  const CODE_ENCODE_TO_JSON_FAILED = 2;
  const CODE_DECODE_FROM_JSON_FAILED = 3;
}