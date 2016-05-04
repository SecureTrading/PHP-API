<?php

namespace Securetrading;

function api(array $configData) {
  return \Securetrading\Stpp\JsonInterface\Main::bootstrap($configData);
}