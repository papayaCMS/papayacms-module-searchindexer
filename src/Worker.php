<?php
/**
 * Search Indexer Worker
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
 * Search Indexer Worker class
 *
 * The worker class provides the actual functionality.
 *
 * @package Papaya-Modules
 * @subpackage SearchIndexer
 */
class PapayaModuleSearchIndexerWorker extends PapayaObject {
  /**
   * Pages connector object
   * @var PagesConnector
   */
  private $_pagesConnector = NULL;

  /**
   * Page object
   * @var base_topic
   */
  private $_page = NULL;

  /**
   * Callback method to be called via action dispatcher whenever a page is published
   *
   * @param array $data
   * @return boolean Success?
   */
  public function onPublishPage($data) {
    $result = TRUE;
    foreach ($data['languages'] as $languageId) {
      $result = $this->indexPage($data['topic_id'], $languageId) && $result;
    }
    return $result;
  }

  /**
   * Index a page in a specific language
   *
   * @param integer $topicId
   * @param integer $languageId
   * @return boolean
   */
  public function indexPage($topicId, $languageId) {
    $content = $this->pagesConnector()->getParsedContents($this->page(), $topicId, $languageId, TRUE, TRUE);
    var_dump($content);
    return TRUE;
  }

  /**
   * Get/set/initialize the Pages Connector
   *
   * @param PagesConnector $pagesConnector optional, default value NULL
   * @return PagesConnector
   */
  public function pagesConnector($pagesConnector = NULL) {
    if ($pagesConnector !== NULL) {
      $this->_pagesConnector = $pagesConnector;
    } elseif ($this->_pagesConnector === NULL) {
      $this->_pagesConnector = $this->papaya()->plugins->get('69db080d0bb7ce20b52b04e7192a60bf');
    }
    return $this->_pagesConnector;
  }

  /**
   * Get/set/initialize the page object
   *
   * @param base_topic $page optional, default value NULL
   * @return base_topic
   */
  public function page(base_topic $page = NULL) {
    if ($page !== NULL) {
      $this->_page = $page;
    } elseif ($this->_page === NULL) {
      $this->_page = new base_topic();
    }
    return $this->_page;
  }
}