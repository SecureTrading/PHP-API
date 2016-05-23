<?php

namespace Securetrading\Stpp\JsonInterface;

class Factory {
  public static function request(\Securetrading\Ioc\IocInterface $ioc, $alias, $requestData) {
    $request = new \Securetrading\Stpp\JsonInterface\Request($ioc);
    $request->setMultiple($requestData);
    return $request;
  }

  public static function requests(\Securetrading\Ioc\IocInterface $ioc, $alias, $params) {
    return new \Securetrading\Stpp\JsonInterface\Requests($ioc);
  }

  public static function response(\Securetrading\Ioc\IocInterface $ioc, $alias, $params) {
    return new \Securetrading\Stpp\JsonInterface\Response($ioc);
  }

  public static function log(\Securetrading\Ioc\IocInterface $ioc, $alias, $params) {
    if ($ioc->hasOption('stpp_json_log_filename')) {
      $params['logFileName'] = $ioc->getOption('stpp_json_log_filename');
    }

    if ($ioc->hasOption('stpp_json_log_filepath')) {
      $params['logFilePath'] = $ioc->getOption('stpp_json_log_filepath');
    }

    if ($ioc->hasOption('stpp_json_log_archive_filepath')) {
      $params['logArchivePath'] = $ioc->getOption('stpp_json_log_archive_filepath');
    }

    if ($ioc->hasOption('stpp_json_log_level')) {
      $params['logLevel'] = $ioc->getOption('stpp_json_log_level');
    }

    $log = new Log();
    $log->setLogger($ioc->get('stLog', $params));
    return $log;
  }

  public static function api(\Securetrading\Ioc\IocInterface $ioc, $alias, $params) {
    $configData = $params;
    $config = $ioc->get('\Securetrading\Stpp\JsonInterface\Config', array($configData));
    return new Api($ioc, $config);
  }

  public static function phrasebook(\Securetrading\Ioc\IocInterface $ioc, $alias, $params) {
    $phrasebookFilepath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'phrasebook.json');
    $phrasebook = $ioc->get('\Securetrading\Stpp\JsonInterface\JsonFileReader')->getContentsAsArray($phrasebookFilepath);
    $config = $ioc->getParameter('config', $params);
    return new Phrasebook($config, $phrasebook);
  }

  public static function translator(\Securetrading\Ioc\IocInterface $ioc, $alias, $params) {
    $messagesFilepath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'errormessages.json');
    $messages = $ioc->get('\Securetrading\Stpp\JsonInterface\JsonFileReader')->getContentsAsArray($messagesFilepath);
    $config = $ioc->getParameter('config', $params);
    $phrasebook = $ioc->get('\Securetrading\Stpp\JsonInterface\Phrasebook', array('config' => $config));
    $log = $ioc->getSingleton('\Securetrading\Stpp\JsonInterface\Log');
    return new Translator($messages, $phrasebook, $log);
  }
}