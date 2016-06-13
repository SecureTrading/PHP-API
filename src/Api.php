<?php

namespace Securetrading\Stpp\JsonInterface;

class Api {
  protected $_ioc;

  protected $_config;

  public function __construct(\Securetrading\Ioc\IocInterface $ioc, ConfigInterface $config) {
    $this->_ioc = $ioc;
    $this->_config = $config;
  }

  public function process($request) {
    $requestReference = 'NOREQREF';
    try {
      $translator = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Translator', array('config' => $this->_config));
      $converter = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Converter', array('config' => $this->_config, 'ioc' => $this->_ioc));
      $httpClient = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Http', array($this->_ioc, $this->_config));

      $request = $this->_verifyRequest($request);

      $requestReference = $request->getSingle('requestreference');
      $this->_getLog()->info("Starting request.");

      $jsonRequestString = $converter->encode($request);
      $httpResponseString = $httpClient->send($jsonRequestString, $requestReference, $this->_getUrl($request));
      $responseObject = $converter->decode($httpResponseString);
      
      $this->_verifyResult($responseObject, $requestReference);
    }
    catch (\Exception $e) {
      $this->_getLog()->alert(sprintf('Exception of type %s caught with code %s in %s on line %s: "%s".', get_class($e), $e->getCode(), $e->getFile(), $e->getLine(), $e->getMessage()));
      $this->_getLog()->alert($e);
      $responseObject = $this->_generateError($e, $requestReference); 
    }

    foreach($responseObject->get('responses') as $response) {
      $defaultMessage = $response->has('errormessage') ? $response->get('errormessage') : '';
      $errorMessage = $translator->translate($response->get('errorcode'), $defaultMessage);
      $response->set('errormessage', $errorMessage);
    }

    $this->_getLog()->info("Finished request.");
    return $responseObject;
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

  protected function _getUrl(AbstractRequest $request) {
    $url = $request->getSingle('datacenterurl', $this->_config->get('datacenterurl'));
    return rtrim($url, '/') . "/json/";
  }

  protected function _verifyResult(\Securetrading\Stpp\JsonInterface\Response $responseObject, $requestReference) {
    if ($responseObject->getSingle('requestreference') !== $requestReference) {
      throw new ApiException(sprintf("Different request reference: sent '%s' but received '%s'.", $requestReference, $responseObject->getSingle('requestreference')), ApiException::CODE_MISMATCHING_REQUEST_REFERENCE);
    }
  }

  protected function _generateError(\Exception $e, $requestReference) {
    $mapper = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\ExceptionMapper');

    list($displayErrorCode, $displayErrorData) = $mapper->getOutputErrorCodeAndData($e);
    
    $responseObject = $this->_ioc->get('\Securetrading\Stpp\JsonInterface\Response');

    $responseObject->fromArray(array(
      'requestreference' => $requestReference,
      'responses' => array(
        array(
          'errorcode' => (string) $displayErrorCode,
	  'errordata' => $displayErrorData,
	  'requesttypedescription' => 'ERROR',
	  'requestreference' => $requestReference
	),
      ),
    ));

    return $responseObject;
  }

  protected function _getLog() {
    return $this->_ioc->getSingleton('\Securetrading\Stpp\JsonInterface\Log');
  }
}
