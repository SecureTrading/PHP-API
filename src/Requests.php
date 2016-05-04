<?php

namespace Securetrading\Stpp\JsonInterface;

class Requests extends AbstractRequest {
  protected $_requests = array();

  public function addRequest(\Securetrading\Stpp\JsonInterface\Request $request) {
    if ($request->hasSingle('datacenterurl')) {
      throw new RequestsException('Request cannot contain the datacenterurl when using the Requests object.', RequestsException::CODE_INDIVIDUAL_REQUEST_HAS_DATACENTERURL);
    }
    $this->_requests[] = $request;;
    return $this;
  }

  public function getRequests() {
    return $this->_requests;
  }
}