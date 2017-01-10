<?php

class PapayaModuleSearchIndexerConnectorConnectionContent {

  public function decodeJSON($content) {
    $return = json_decode($content);
    if (is_null($return)) {
      throw new PapayaModuleSearchIndexerConnectorConnectionContentJsonException();
    }
    return $return;
  }
}