<?php

namespace Securetrading\Stpp\JsonInterface;

class Request extends AbstractRequest {
  protected function _setCachetoken($cacheToken) {
    $base64Decoded = base64_decode($cacheToken);
    $jsonDecoded = json_decode($base64Decoded);

    if (json_last_error() === JSON_ERROR_NONE) {
      $entries = get_object_vars($jsonDecoded);

      if (isset($entries['cachetoken'])) {
	$cacheToken = $entries['cachetoken'];
	unset($entries['cachetoken']);
      }

      foreach($entries as $k => $v) {
	$this->setSingle($k, $v);
      }
    }

    $this->_set('cachetoken', $cacheToken);
  }
}