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
class PapayaModuleElasticsearchSearchConnector extends base_connector {
  /**
   * The module's own GUID
   */
  const MODULE_GUID = '0a6800488f9760b93dd9ff17ee03c28a';

  private $_worker = NULL;

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

  public function search($term, $language, $limit = 10, $offset = 0) {
    try {
      return $result =  $this->worker()->search($term, $language, $limit, $offset);
    } catch (PapayaModuleElasticsearchException $error) {
      return $error->json();
    }
  }

  /**
   * Get/set/initialize the worker
   *
   * @param PapayaModuleElasticsearchSearchWorker optional, default value NULL
   * @return PapayaModuleElasticsearchSearchWorker
   */
  public function worker(PapayaModuleElasticsearchSearchWorker $worker = NULL) {
    if ($worker !== NULL) {
      $this->_worker = $worker;
    } elseif ($this->_worker === NULL) {
      $this->_worker = new PapayaModuleElasticsearchSearchWorker();
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