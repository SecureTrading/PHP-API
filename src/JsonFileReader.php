<?php

namespace Securetrading\Stpp\JsonInterface;

class JsonFileReader
{
  public function getContentsAsArray($filePath) {
    $contents = file_get_contents($filePath);

    if (!$contents) {
      throw new JsonFileReaderException(sprintf("Contents could not be loaded for file '%s'.", $filePath), JsonFileReaderException::CODE_FILE_NOT_READ);
    }
    
    $decodedJsonAsArray = json_decode($contents, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new JsonFileReaderException(sprintf("Unable to decode file '%s' from JSON.", $filePath), JsonFileReaderException::CODE_FILE_JSON_ERROR);
    }

    return $decodedJsonAsArray;
  }
}