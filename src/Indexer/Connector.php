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
 * @subpackage Elasticsearch
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
 * @subpackage Elasticsearch
 */
class PapayaModuleElasticsearchIndexerConnector extends base_connector {
  /**
   * The module's own GUID
   */
  const MODULE_GUID = '1eb06c29ba114ca2804be4bda69375e7';

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
      'PAGE_CONTENT_CONTAINER' => [
          'Content container',
          'isSomeText',
          FALSE,
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
   * The worker object
   * @var PapayaModuleElasticsearchIndexerWorker
   */
  private $_worker = NULL;

  /**
   * Callback method to be called via action dispatcher whenever a page is published
   *
   * @param array $data
   * @return boolean Success?
   */
  public function onPublishPage($data) {
    return $this->worker()->onPublishPage($data);
  }

  /**
   * Callback method to be called via action dispatcher whenever a specific page translation is unpublished
   *
   * @param array $data
   * @return boolean Success?
   */
  public function onUnpublishPage($data) {
    return $this->worker()->onUnpublishPage($data);
  }

  /**
   * Callback method to be called via action dispatcher whenever a lot of pages should be removed from index
   * @param array $data
   * @return boolean
   */
  public function onDeletePages($data) {
    return $this->worker()->onDeletePages($data);
  }

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
    $result = $this->worker()->addToIndex($topicId, $identifier, $url, $content, $title, $itemId);
    $searchItemId = $result;
    if ($searchItemId === FALSE) {
      $searchItemId = $this->worker()->lastSearchItemId();
    }
    return $searchItemId;
  }

  /**
   * Remove a node from the index
   *
   * @param string $nodeId
   * @param string $identifier Language identifier
   * @return bool
   */
  public function removeFromIndex($nodeId, $identifier) {
    return $this->worker()->removeFromIndex($nodeId, $identifier);
  }

  /**
   * Get/set/initialize the worker
   *
   * @param PapayaModuleElasticsearchIndexerWorker optional, default value NULL
   * @return PapayaModuleElasticsearchIndexerWorker
   */
  public function worker(PapayaModuleElasticsearchIndexerWorker $worker = NULL) {
    if ($worker !== NULL) {
      $this->_worker = $worker;
    } elseif ($this->_worker === NULL) {
      $this->_worker = new PapayaModuleElasticsearchIndexerWorker();
      $this->_worker->papaya($this->papaya());
    }
    return $this->_worker;
  }

  /**
   * Get a module option
   *
   * @param string $option
   * @param mixed $default optional, default value NULL
   * @return mixed
   */
  public function option($option, $default) {
    return $this->worker()->option($option, $default);
  }
}
