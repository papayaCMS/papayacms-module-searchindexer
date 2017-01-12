<?php

class PapayaModuleElasticsearchConnectionContent {

  public function decodeJSON($content) {
    $return = json_decode($content);
    if (is_null($return)) {
      throw new PapayaModuleElasticsearchConnectionContentJsonException();
    }
    return $return;
  }
}