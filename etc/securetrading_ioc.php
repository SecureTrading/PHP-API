<?php

return array(
  'stStppJson' => array(
    'definitions' => array(
      '\Securetrading\Stpp\JsonInterface\Log' => array('\Securetrading\Stpp\JsonInterface\Factory', 'log'),
      '\Securetrading\Stpp\JsonInterface\Api' => array('\Securetrading\Stpp\JsonInterface\Factory', 'api'),
      'jsonApi' => array('\Securetrading\Stpp\JsonInterface\Factory', 'api'),
      '\Securetrading\Stpp\JsonInterface\Request' => array('\Securetrading\Stpp\JsonInterface\Factory', 'request'),
      'jsonRequest' => array('\Securetrading\Stpp\JsonInterface\Factory', 'request'),
      '\Securetrading\Stpp\JsonInterface\Requests' => array('\Securetrading\Stpp\JsonInterface\Factory', 'requests'),
      'jsonRequests' => array('\Securetrading\Stpp\JsonInterface\Factory', 'requests'),
      '\Securetrading\Stpp\JsonInterface\Response' => array('\Securetrading\Stpp\JsonInterface\Factory', 'response'),
      '\Securetrading\Stpp\JsonInterface\Phrasebook' => array('\Securetrading\Stpp\JsonInterface\Factory', 'phrasebook'),
      '\Securetrading\Stpp\JsonInterface\Translator' => array('\Securetrading\Stpp\JsonInterface\Factory', 'translator'),
    ),
    'dependencies' => array(
      'stHttp',
      'stLog',
    ),
  ),
);