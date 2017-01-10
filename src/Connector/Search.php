<?php
/**
 * Search Indexer Connector
 *
 * @copyright by dimensional GmbH, Cologne, Germany - All rights reserved.
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 * You can redistribute and/or modify this script under the terms of the GNU General Public
 * License (GPL) version 2, provided that the copyright and license notes, including these
 * lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.
 *
 * @package Papaya-Modules
 * @subpackage SearchIndexer
 * @version $Id: Api.php 39861 2014-06-27 09:38:58Z kersken $
 */

/**
 * Search Indexer Connector class
 *
 * This connector is the public interface for all of the search indexer functionality.
 *
 * To get an instance: $connector = $this->papaya()->plugins->get('1eb06c29ba114ca2804be4bda69375e7');
 *
 * @package Papaya-Modules
 * @subpackage SearchIndexer
 */
class PapayaModuleSearchIndexerConnectorSearch extends base_connector {
  /**
   * The module's own GUID
   */
  const MODULE_GUID = '0a6800488f9760b93dd9ff17ee03c28a';

  /**
   * @var mixed
   */
  private $_moduleOptions = NULL;

  private $connection = NULL;
  private $content = NULL;

  /**
   * Module option definitions
   * @var array
   */
  public $pluginOptionFields = [
    'ELASTICSEARCH_HOST' => [
      'ElasticSearch Host',
      'isSomeText',
      TRUE,
      'input',
      200,
      '',
      'localhost'
    ],
    'ELASTICSEARCH_PORT' => [
      'ElasticSearch Port',
      'isNum',
      TRUE,
      'input',
      5,
      '',
      9200
    ],
    'ELASTICSEARCH_INDEX' => [
      'ElasticSearch Index',
      'isSomeText',
      TRUE,
      'input',
      100
    ],
    'OUTPUT_MODE' => [
      'Output Mode',
      'isSomeText',
      TRUE,
      'input',
      30,
      '',
      'html'
    ]
  ];

  /**
   * @param string $term
   * @param $language
   * @param int $limit
   * @param int $offset
   * @return bool|http-result
   * @throws PapayaModuleSearchIndexerConnectorException
   */
  public function search($term = '', $language, $limit = 10, $offset = 0) {

    $return = FALSE;

    $host = $this->option('ELASTICSEARCH_HOST', 'localhost');
    $port = $this->option('ELASTICSEARCH_PORT', 9200);
    $index = $this->option('ELASTICSEARCH_INDEX', 'index');
    $url = sprintf("http://%s:%d/%s/%s/_search", $host, $port, $index, $language);

    if (!empty($term)) {
      $term = preg_replace('(^\W+)u', '', $term);
      $term = preg_replace('(\W+$)u', '', $term);
      $activeTerm = $term;
      if (!preg_match('(\s)', $activeTerm)) {
        $activeTerm = sprintf('*%s*', $activeTerm);
      }
      $rawQuery = [
          'from' => $offset,
          'size' => $limit,
          'query' => [
              'query_string' => [
                  'query' => $activeTerm,
                  'fields' => ['title^2', 'content']
              ]
          ],
          'highlight' => [
              'fields' => [
                  'content' => new stdClass()
              ]
          ]
      ];
      $query = json_encode($rawQuery);
      $options = [
          'http' => [
              'method' => 'GET',
              'header' => "Content-type: application/json\r\nContent-length: " . strlen($query),
              'content' => $query
          ]
      ];

      try {

        $this->connection()->open($url, $options);
        $content = $this->connection()->getContent();
        $return = $this->content()->decodeJSON($content);

      } catch(PapayaModuleSearchIndexerConnectorException $error) {
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
   * Get a module option
   *
   * @param string $option
   * @param mixed $default optional, default value NULL
   * @return mixed
   */
  public function option($option, $default = NULL) {
    if ($this->_moduleOptions === NULL) {
      $this->_moduleOptions = $this->papaya()->plugins->options[self::MODULE_GUID];
    }
    return $this->_moduleOptions->get($option, $default);
  }

  /**
   * @param PapayaModuleSearchIndexerConnectorConnection $connection
   * @return PapayaModuleSearchIndexerConnectorConnection
   */
  public function connection(PapayaModuleSearchIndexerConnectorConnection $connection = NULL) {
    if (isset($connection)) {
      $this->connection = $connection;
    } else if (is_null($this->connection)) {
      $this->connection = new PapayaModuleSearchIndexerConnectorConnection();
    }
    return $this->connection;
  }

  /**
   * @param PapayaModuleSearchIndexerConnectorConnectionContent $content
   * @return PapayaModuleSearchIndexerConnectorConnectionContent
   */
  public function content(PapayaModuleSearchIndexerConnectorConnectionContent $content = NULL) {
    if (isset($content)) {
      $this->content = $content;
    } else if (is_null($this->content)) {
      $this->content = new PapayaModuleSearchIndexerConnectorConnectionContent();
    }
    return $this->content;
  }
}