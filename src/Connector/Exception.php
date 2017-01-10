<?php

abstract class PapayaModuleSearchIndexerConnectorException extends PapayaException {

  public function __toString() {
    return $this->getCode().$this->getLine().$this->getMessage();
  }

  public function appendTo(PapayaXmlElement $root) {

    $root->appendElement('type', array(), get_class($this));
    //$root->appendElement('trace', array(), $this->getTraceAsString());
    $root->appendElement('code', array(), $this->getCode());
    $root->appendElement('line', array(), $this->getLine());
    $root->appendElement('message', array(), $this->getMessage());

  }

}