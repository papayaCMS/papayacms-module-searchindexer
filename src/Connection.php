<?php

class PapayaModuleElasticsearchConnection {

  private $connection = NULL;

  public function open($url, $options) {

    $context = stream_context_create($options);
    $this->connection = @fopen($url, 'r', FALSE, $context);

    if (!$this->connection){
      throw new PapayaModuleElasticsearchConnectionException($url);
    }

    return $this->connection;
  }

  public function getContent() {
    $content = stream_get_contents($this->connection);
    if (!$content) {
      throw new PapayaModuleElasticsearchConnectionContentException();
    }

    return $content;
  }

  public function get() {
    return $this->connection;
  }

  public function escapeTerm($term) {
    // remove < and >
    $result = str_replace(['<', '>'], '', $term);
    // prefix special characters with backslash
    $result = preg_replace_callback(
      '([-+=!(){}[\\]^"~*?:\\\\/]|&&|\\|\\|)',
      static function($match) {
        return '\\'.$match[0];
      },
      $result
    );
    return $result;
  }
}
