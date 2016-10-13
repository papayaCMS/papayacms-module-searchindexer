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
 * @subpackage SearchIndexer
 * @version $Id: Api.php 39861 2014-06-27 09:38:58Z kersken $
 */

/**
 * Search Indexer Writer class
 *
 * The writer class connects to ElasticSearch and adds a document to the index.
 *
 * @package Papaya-Modules
 * @subpackage SearchIndexer
 */
class PapayaModuleSearchIndexerWriter {
  /**
   * Owner object
   * @var PapayaModuleSearchIndexerWorker
   */
  private $_owner = NULL;

  /**
   * Add content and its URL to the index
   *
   * @param int $topicId Page ID
   * @param string $identifier Language identifier
   * @param string $url Public URL of the document
   * @param string $content Content to index
   * @param string $title Page title (may be searched with higher priority)
   * @return bool
   */
  public function addToIndex($topicId, $identifier, $url, $content, $title) {
    $result = FALSE;
    $searchHost = $this->owner()->option('ELASTICSEARCH_HOST');
    $searchPort = $this->owner()->option('ELASTICSEARCH_PORT');
    $searchIndex = $this->owner()->option('ELASTICSEARCH_INDEX');
    $rawData = [
      'url' => $url,
      'title' => $title,
      'content' => $this->prepareContent($content)
    ];
    $data = json_encode($rawData);
    $urlPath = 'http://'.$searchHost.':'.$searchPort.'/'.$searchIndex.'/'.$identifier.'/'.$topicId;
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
      $result = (FALSE !== fwrite($connection, $data));
    }
    return $result;
  }

  /**
   * Get/set the owner
   *
   * @param PapayaModuleSearchIndexerWorker $owner optional, default value NULL
   * @return PapayaModuleSearchIndexerWorker
   */
  public function owner($owner = NULL) {
    if ($owner !== NULL) {
      $this->_owner = $owner;
    }
    return $this->_owner;
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
}