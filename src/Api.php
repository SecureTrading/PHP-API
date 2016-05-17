<?php

namespace Securetrading\Stpp\JsonInterface;

class Api {
  protected $_ioc;

  protected $_config;

  public function __construct(\Securetrading\Ioc\IocInterface $ioc, \Securetrading\Config\ConfigInterface $config) {
    $this->_ioc = $ioc;
    $this->_config = $config;
  }

  public function process($request) {
    $requestReference = 'NOREQREF';
    try {
      $request = $this->_verifyRequest($request);
      $this->_convertCharacterEncodingOfRequest($request);

      $requestReference = $request->getSingle('requestreference');
      $this->_getLog()->info("Starting request.");
      
      $url = $request->getSingle('datacenterurl', $this->_config->get('datacenterurl'));
      $url = rtrim($url, '/') . "/json/";
      
      $converter = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Converter', array('config' => $this->_config, 'ioc' => $this->_ioc));
      $jsonRequestString = $converter->encode($request);

      $httpClient = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Http', array('config' => $this->_config));
      $httpResponseString = $httpClient->send($jsonRequestString, $requestReference, $url);

      $responseObject = $converter->decode($httpResponseString);
      $this->_verifyResult($responseObject, $requestReference);
    }
    catch (\Exception $e) {
      $this->_getLog()->alert(sprintf('Exception of type %s caught with code %s in %s on line %s: "%s".', get_class($e), $e->getCode(), $e->getFile(), $e->getLine(), $e->getMessage()));
      $this->_getLog()->alert($e);
      $responseObject = $this->_generateError($e, $requestReference); 
    }

    $this->_getLog()->info("Finished request.");
    return $responseObject;
  }

  protected function _getLog() {
    return $this->_ioc->getSingleton('\Securetrading\Stpp\JsonInterface\Log');
  }

  protected function _convertData($data) {
    if (is_array($data) || $data instanceof \Traversable) {
      foreach($data as $k => $v) {
	$data[$k] = $this->_convertData($v);
      }
      $returnValue = $data;
    }
    else {
      $returnValue = iconv($this->_config->get('input_encoding'), 'utf-8', $data);
    }
    return $returnValue;
  }

  protected function _convertCharacterEncodingOfRequest(\Securetrading\Stpp\JsonInterface\AbstractRequest $request) {
    if ($request instanceof \Securetrading\Stpp\JsonInterface\Requests) {
      foreach($request->getRequests() as $request) {
	$this->_convertData($request);
      }
    }
    else {
      $this->_convertData($request);
    }
  }

  protected function _verifyRequest($request) {
    if (is_array($request)) {
      $createdRequest = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Request');
      $createdRequest->set($request);
      $request = $createdRequest;
    }
    else if (!$request instanceof \Securetrading\Stpp\JsonInterface\Request && !$request instanceof \Securetrading\Stpp\JsonInterface\Requests) {
      throw new ApiException('Invalid request type.', ApiException::CODE_INVALID_REQUEST_TYPE);
    }
    return $request;
  }

  protected function _verifyResult(\Securetrading\Stpp\JsonInterface\Response $responseObject, $requestReference) {
    if ($responseObject->getSingle('requestreference') !== $requestReference) {
      throw new ApiException(sprintf("Different request reference: sent '%s' but received '%s'.", $requestReference, $responseObject->getSingle('requestreference')), ApiException::CODE_MISMATCHING_REQUEST_REFERENCE);
    }
    return $this;
  }

  protected function _generateError(\Exception $e, $requestReference) {
    $mapper = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\ExceptionMapper', array('config' => $this->_config));

    list($displayErrorCode, $displayErrorMessage, $displayErrorData) = $mapper->getOutputErrorMessage($e);
    
    $responseObject = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Response');
    $responseObject->setMultiple(array(
      'requestreference' => $requestReference,
      'responses' => array(
        array(
          'errorcode' => (string) $displayErrorCode,
          'errormessage' => $displayErrorMessage,
	  'errordata' => $displayErrorData,
	  'requesttypedescription' => 'ERROR',
	  'requestreference' => $requestReference
	),
      ),
    ));

    return $responseObject;
  }
}
