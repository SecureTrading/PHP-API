<?php

namespace Securetrading\Stpp\JsonInterface;

interface ConfigInterface {
  function get($key);
  function toArray();
}