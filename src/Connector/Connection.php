<?php

class PapayaModuleSearchIndexerConnectorConnection {

  private $connection = NULL;

  public function open($url, $options) {

    $context = stream_context_create($options);
    $this->connection = @fopen($url, 'r', FALSE, $context);

    if (!$this->connection){
      throw new PapayaModuleSearchIndexerConnectorConnectionException($url);
    }

    return $this->connection;
  }

  public function getContent() {
    $content = stream_get_contents($this->connection);
    if (!$content) {
      throw new PapayaModuleSearchIndexerConnectorConnectionContentException();
    }

    return $content;
  }

  public function get() {
    return $this->connection;
  }
}