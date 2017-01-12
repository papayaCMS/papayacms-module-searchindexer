<?php

abstract class PapayaModuleElasticsearchException extends PapayaException {

  public function __toString() {
    return $this->getCode().$this->getLine().$this->getMessage();
  }

  public function appendTo(PapayaXmlElement $root) {

    $root->appendElement('type', array(), get_class($this));
    $root->appendElement('code', array(), $this->getCode());
    $root->appendElement('line', array(), $this->getLine());
    $root->appendElement('message', array(), $this->getMessage());

  }

  public function json() {
    $json =  [
      'error' => [
          'type' => get_class($this),
          'code' => $this->getCode(),
          'line' => $this->getLine(),
          'message' => $this->getMessage()
      ]
    ];

    return json_encode($json);
  }
}