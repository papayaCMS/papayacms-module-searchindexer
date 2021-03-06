<?php
/**
 * Search Indexer Writer
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
 * @subpackage Elasticsearch
 * @version $Id: Api.php 39861 2014-06-27 09:38:58Z kersken $
 */

/**
 * Search Indexer Writer class
 *
 * The writer class connects to ElasticSearch and adds a document to the index.
 *
 * @package Papaya-Modules
 * @subpackage Elasticsearch
 */
class PapayaModuleElasticsearchIndexerWriter {
  /**
   * Owner object
   * @var PapayaModuleElasticsearchIndexerWorker
   */
  private $_owner = NULL;

  /**
   * Last search item ID created
   * @var string
   */
  private $_lastSearchItemId = '';

  /**
   * @var PapayaModuleElasticsearchConnection
   */
  private $connection = NULL;

  /**
   * Add content and its URL to the index
   *
   * @param int $topicId Page ID
   * @param string $identifier Language identifier
   * @param string $url Public URL of the document
   * @param string $content Content to index
   * @param string $title Page title (may be searched with higher priority)
   * @param string $itemId optional, default NULL
   * @return mixed new node ID on success, bool FALSE otherwise
   */
  public function addToIndex($topicId, $identifier, $url, $content, $title, $itemId = NULL) {
    $result = FALSE;
    $searchHost = $this->owner()->option('ELASTICSEARCH_HOST');
    $searchPort = $this->owner()->option('ELASTICSEARCH_PORT');
    $searchIndex = $this->owner()->option('ELASTICSEARCH_INDEX');
    $rawData = [
      'url' => $url,
      'title' => $title,
      'content' => $this->prepareContent($content),
      'content_suggest' => [ 'input' => $this->getKeywordsFromContent($this->prepareContent($content)) ]
    ];
    $data = json_encode($rawData);
    $searchItemId = $topicId;
    if ($itemId !== NULL) {
      $itemId = preg_replace('(\W)', '', $itemId);
      if (empty($itemId)) {
        $itemId = md5(rand());
      }
      $searchItemId = sprintf("%s.%d", $itemId, $topicId);
    }
    $this->_lastSearchItemId = $searchItemId;
    $urlPath = 'http://'.$searchHost.':'.$searchPort.'/'.$searchIndex.'/'.$identifier.'/'.$searchItemId;
    $options = [
      'http' => [
        'method' => 'PUT',
        'header' => "Content-type: application/json\r\nContent-length: ".strlen($data),
        'content' => $data
      ]
    ];

    $context = stream_context_create($options);
    $connection = @fopen($urlPath, 'r', FALSE, $context);
    if (is_resource($connection)) {
      if (FALSE !== fwrite($connection, $data)) {
        $result = $searchItemId;
      }
    }
    return $result;
  }

  /**
   * Remove a node from the index
   *
   * @param string $nodeId
   * @param string $identifier Language identifier
   * @return bool
   */
  public function removeFromIndex($nodeId, $identifier) {
    $result = FALSE;
    $searchHost = $this->owner()->option('ELASTICSEARCH_HOST');
    $searchPort = $this->owner()->option('ELASTICSEARCH_PORT');
    $searchIndex = $this->owner()->option('ELASTICSEARCH_INDEX');
    $urlPath = 'http://'.$searchHost.':'.$searchPort.'/'.$searchIndex.'/'.$identifier.'/'.$nodeId;
    $options = [
      'http' => [
        'method' => 'DELETE'
      ]
    ];
    $context = stream_context_create($options);
    $connection = @fopen($urlPath, 'r', FALSE, $context);
    if (is_resource($connection)) {
      $result = TRUE;
    }
    return $result;
  }

  /**
   * Get/set the owner
   *
   * @param PapayaModuleElasticsearchIndexerWorker $owner optional, default value NULL
   * @return PapayaModuleElasticsearchIndexerWorker
   */
  public function owner($owner = NULL) {
    if ($owner !== NULL) {
      $this->_owner = $owner;
    }
    return $this->_owner;
  }

  /**
   * Get the last search item ID
   *
   * @return string
   */
  public function lastSearchItemId() {
    return $this->_lastSearchItemId;
  }

  /**
   * Prepare content
   *
   * @param string $content
   * @return string
   */
  protected function prepareContent($content) {
    $content = str_replace('>', '> ', $content);
    $content = strip_tags($content);
    $content = preg_replace("(([\r ]*\n[\r ]*)+)", "\n", $content);
    $content = preg_replace("( +)", " ", $content);
    return trim($content);
  }

  protected function getKeywordsFromContent($content) {
    $json = ['none'];
    preg_match_all('(\b\p{Lu}\pL{3,})u', $content, $matches);

    $keywords = $matches[0];
    if (count($keywords) > 0) {
      $uniqueKeywords = array();
      foreach ($keywords as $word) {
        if (!in_array($word, $uniqueKeywords)) {
          $uniqueKeywords[] = $word;
        }
      }
    }
    if (count($uniqueKeywords) > 0) {
      $json = $uniqueKeywords;
    }
    return $json;
  }

  /**
   * @param PapayaModuleElasticsearchConnection $connection
   * @return PapayaModuleElasticsearchConnection
   */
  public function connection(PapayaModuleElasticsearchConnection $connection = NULL) {
    if (isset($connection)) {
      $this->connection = $connection;
    } else if (is_null($this->connection)) {
      $this->connection = new PapayaModuleElasticsearchConnection();
    }
    return $this->connection;
  }
}