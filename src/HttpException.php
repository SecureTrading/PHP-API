<?php

namespace Securetrading\Stpp\JsonInterface;

class HttpException extends \Securetrading\Exception {
  const CODE_CURL_ERROR = 1;
  const CODE_GENERIC_INVALID_HTTP_STATUS = 2;
  const CODE_401_INVALID_HTTP_STATUS = 3;
}