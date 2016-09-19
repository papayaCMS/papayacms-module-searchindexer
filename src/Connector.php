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
class PapayaModuleSearchIndexerConnector extends base_connector {
  /**
   * The worker object
   * @var PapayaModuleSearchIndexerWorker
   */
  private $_worker = NULL;

  /**
   * Callback method to be called via action dispatcher whenever a page is published
   *
   * @param array $data
   * @return boolean Success?
   */
  public function onPublishPage($data) {
    return $this->_worker->onPublishPage($data);
  }

  /**
   * Get/set/initialize the worker
   *
   * @param PapayaModuleSearchIndexerWorker optional, default value NULL
   * @return PapayaModuleSearchIndexerWorker
   */
  public function worker(PapayaModuleSearchIndexerWorker $worker = NULL) {
    if ($worker !== NULL) {
      $this->_worker = $worker;
    } elseif ($this->_worker === NULL) {
      $this->_worker = new PapayaModuleSearchIndexerWorker();
    }
    return $this->_worker;
  }
}