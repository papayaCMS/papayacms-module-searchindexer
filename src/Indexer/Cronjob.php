<?php
/**
 * Elasticsearch Indexer Cronjob
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
 * Search Indexer Cronjob class
 *
 * The cronjob class provides indexing of all pages as a cronjob.
 *
 * @package Papaya-Modules
 * @subpackage Elasticsearch
 */
class PapayaModuleElasticsearchIndexerCronjob extends base_cronjob {
  /**
   * Configuration edit fields
   * @var array
   */
  public $editFields = [
    'base_url' => [
      'Base URL',
      'isHTTPX',
      TRUE,
      'input',
      1024,
      'The cronjob is not necessarily executed in  a web context, so it needs a base url.',
      ''
    ],
    'indexed_timestamp' => [
      'Reindex pages older than',
      'isNum',
      TRUE,
      'input',
      10,
      'The cronjob will reindex pages older than the given number of seconds.',
      21600
    ],
    'error_timestamp' => [
      'Reindex error pages older than',
      'isNum',
      TRUE,
      'input',
      10,
      'The cronjob will attempt to reindex error pages older than the given number of seconds.',
      86400
    ]
  ];

  /**
   * Worker object
   * @var PapayaModuleElasticsearchIndexerWorker
   */
  private $_worker = NULL;

  /**
   * Execute
   *
   * @return mixed integer 0 on success, otherwise string error message
   */
  public function execute() {
    $this->setDefaultData();
    $this->worker()->indexAllPages(
      FALSE,
      $this->data['indexed_timestamp'],
      $this->data['error_timestamp'],
      $this->data['base_url']
    );
    $info = $this->worker()->info();
    $infoString = sprintf(
      "Total: %d,
Attempted: %d,
Success: %d,
Errors: %d,
Skipped (already indexed): %d,
Skipped (errors): %d\n",
      $info['total'],
      $info['attempted'],
      $info['success'],
      $info['errors'],
      $info['skipped_indexed'],
      $info['skipped_errors']
    );
    echo $infoString;
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_MODULES,
      PapayaMessageLogable::SEVERITY_INFO,
      sprintf("Indexing pages -- %s", $infoString)
    );
    $this->papaya()->messages->dispatch($message);
    return 0;
  }

  /**
   * Get/set/initialize the worker object
   *
   * @param PapayaModuleElasticsearchIndexerWorker $worker optional, default value NULL
   * @return PapayaModuleElasticsearchIndexerWorker
   */
  public function worker($worker = NULL) {
    if ($worker !== NULL) {
      $this->_worker = $worker;
    } elseif ($this->_worker === NULL) {
      $this->_worker = new PapayaModuleElasticsearchIndexerWorker();
    }
    return $this->_worker;
  }


  /**
   * Check execution parameters
   *
   * @return bool|string
   */
  public function checkExecParams() {
    $result = FALSE;
    $this->setDefaultData();
    if ($this->data['indexed_timestamp'] > 0 && $this->data['error_timestamp'] > 0) {
      $result = sprintf(
        "Pages: %d, error pages: %d\n",
        $this->data['indexed_timestamp'],
        $this->data['error_timestamp']
      );
      if ($this->data['error_timestamp'] <= $this->data['indexed_timestamp']) {
        $result .= "Warning: Reindex error pages should be greater than reindex value.\n";
      }
    }
    return $result;
  }
}
