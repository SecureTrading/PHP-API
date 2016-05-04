<?php

namespace Securetrading\Stpp\JsonInterface;

class ApiException extends \Securetrading\Exception {
  const CODE_INVALID_REQUEST_TYPE = 1;
  const CODE_MISMATCHING_REQUEST_REFERENCE = 2;
}