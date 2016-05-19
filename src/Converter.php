<?php

namespace Securetrading\Stpp\JsonInterface;

class Converter {
  protected $_config;

  protected $_ioc;

  public function __construct(ConfigInterface $config, \Securetrading\Ioc\IocInterface $ioc) {
    $this->_config = $config;
    $this->_ioc = $ioc;
  }

  public function encode(\Securetrading\Stpp\JsonInterface\AbstractRequest $inputRequest) {
    $this->_getLog()->debug("Starting encoding.");

    if ($inputRequest instanceof \Securetrading\Stpp\JsonInterface\Request) {
      $this->_getLog()->debug("Instance of \Securetrading\Stpp\JsonInterface\Request detected.");
      $requests = array($inputRequest);
    }
    else if ($inputRequest instanceof \Securetrading\Stpp\JsonInterface\Requests) {
      $this->_getLog()->debug("Instance of \Securetrading\Stpp\JsonInterface\Requests detected.");
      $requests = $inputRequest->getRequests();
    }
    else { // Note - Just a failsafe in case we ever extend \Securetrading\Stpp\JsonInterface\AbstractRequest with another child class.
      throw new ConverterException(sprintf("Instances of '%s' cannot be handled by this function.", get_class($inputRequest)), ConverterException::CODE_ENCODE_INVALID_REQUEST_TYPE);
    }
    
    foreach($requests as $index => $request) {
      $requests[$index] = $request->toArray();
    }
    
    $stRequest = array(
      "alias" => $this->_config->get('username'),
      "version" => $this->_config->get('jsonversion'),
      "request" => $requests,
      "libraryversion" => sprintf("php_%s", Main::FULL_VERSION),
    );

    $jsonEncodedRequest = json_encode($stRequest);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new ConverterException('Unable to convert to JSON.', ConverterException::CODE_ENCODE_TO_JSON_FAILED);
    }
    
    $this->_getLog()->debug("Finished encoding.");

    return $jsonEncodedRequest;
  }

  public function decode($jsonResponseString) {
    $this->_getLog()->debug("Starting decoding.");

    $decodedResponse = json_decode($jsonResponseString, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new ConverterException('Unable to decode from JSON.', ConverterException::CODE_DECODE_FROM_JSON_FAILED);
    }

    $responseObject = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Response');

    $responseObject->fromArray(array(
      'requestreference' => $decodedResponse['requestreference'],
      'version' => $decodedResponse['version'],
      'responses' => $decodedResponse['response'],
    ));

    $this->_getLog()->debug("Finished decoding.");

    return $responseObject;
  }

  protected function _getLog() {
    return $this->_ioc->getSingleton('\Securetrading\Stpp\JsonInterface\Log');
  }
}