<?php

class PapayaModuleElasticsearchSuggestionWorker extends PapayaObject {

  private $_moduleOptions = NULL;
  private $_content = NULL;
  private $_connection = NULL;

  /**
   * @param string $term
   * @param $language
   * @param $term
   * @return bool|http-result
   * @throws PapayaModuleElasticsearchException
   */
  public function suggest($term = '', $language) {

    $return = FALSE;

    $host = $this->option('ELASTICSEARCH_HOST', 'localhost');
    $port = $this->option('ELASTICSEARCH_PORT', 9200);
    $index = $this->option('ELASTICSEARCH_INDEX', 'index');

    $url = sprintf("http://%s:%d/%s/%s/_search", $host, $port, $index, $language);

    if (!empty($term)) {
      $term = preg_replace('(^\s+)u', '', $term);
      $term = preg_replace('(\s+$)u', '', $term);
      $activeTerm = strtolower($term);
      if (!preg_match('(\s|\\*)', $activeTerm)) {
        $queryString = sprintf('%s.*', $this->connection()->escapeTerm($activeTerm));
      } else {
        $queryString = $this->connection()->escapeTerm($activeTerm);
      }

      $rawQuery = [
        'size'=> 0,
        'aggregations'=> [
          'didYouMean' => [
            'terms' => [
              'field' => 'didYouMean',
              'order' => [
                '_count' => 'desc'
              ],
              'include' => $queryString,
              'size' => $this->option('SUGGESTER_LIMIT', 10)
            ]
          ],
          'autocomplete' => [
            'terms' => [
              'field' => 'autocomplete',
              'order' => [
                '_count' => 'desc'
              ],
              'include' => $queryString,
              'size' => $this->option('SUGGESTER_LIMIT', 10)
            ]
          ],
        ]
      ];

      $query = json_encode($rawQuery);
      $options = [
          'http' => [
              'method' => 'GET',
              // 'ignore_errors' => true,
              'header' => "Content-type: application/json\r\nContent-length: " . strlen($query),
              'content' => $query
          ]
      ];

      try {

        $this->connection()->open($url, $options);
        $content = $this->connection()->getContent();
        $return = $this->content()->decodeJSON($content);

      } catch(PapayaModuleElasticsearchException $error) {
        $message = new PapayaMessageLog(
            PapayaMessageLogable::GROUP_MODULES,
            PapayaMessageLogable::SEVERITY_ERROR,
            $error->getMessage()
        );
        $this->papaya()->messages->dispatch($message);
        throw $error;
      }
    }
    return $return;
  }

  /**
   * @param PapayaModuleElasticsearchConnection $connection
   * @return PapayaModuleElasticsearchConnection
   */
  public function connection(PapayaModuleElasticsearchConnection $connection = NULL) {
    if (isset($connection)) {
      $this->_connection = $connection;
    } else if (is_null($this->_connection)) {
      $this->_connection = new PapayaModuleElasticsearchConnection();
    }
    return $this->_connection;
  }

  /**
   * @param PapayaModuleElasticsearchConnectionContent $content
   * @return PapayaModuleElasticsearchConnectionContent
   */
  public function content(PapayaModuleElasticsearchConnectionContent $content = NULL) {
    if (isset($content)) {
      $this->_content = $content;
    } else if (is_null($this->_content)) {
      $this->_content = new PapayaModuleElasticsearchConnectionContent();
    }
    return $this->_content;
  }

  /**
   * Get a module option
   *
   * @param string $option
   * @param mixed $default optional, default value NULL
   * @return mixed
   */
  public function option($option, $default = NULL) {
    if ($this->_moduleOptions === NULL) {
      $this->_moduleOptions = $this->papaya()->plugins->options[PapayaModuleElasticsearchSuggestionConnector::MODULE_GUID];
    }
    return $this->_moduleOptions->get($option, $default);
  }
}
