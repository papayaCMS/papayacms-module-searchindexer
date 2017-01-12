<?php
/**
 * Search Indexer Result Page Content
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
 * Search Indexer Result Page Content class
 *
 * The result page content class does the actual querying and result displaying.
 *
 * @package Papaya-Modules
 * @subpackage Elasticsearch
 */
class PapayaModuleElasticsearchSearchResultPageContent {
  /**
   * @var PapayaModuleElasticsearchSearchResultPage
   */
  private $_owner = NULL;
  private $_searchWorker = NULL;
  private $_paging = NULL;
  private $_results = NULL;

  /**
   * @param PapayaModuleElasticsearchSearchResultPage $owner
   */
  public function __construct(PapayaModuleElasticsearchSearchResultPage $owner) {
    $this->_owner = $owner;
  }

  /**
   * @return PapayaPluginEditableContent
   */
  public function content() {
    return $this->getOwner()->content();
  }

  /**
   * @return PapayaModuleElasticsearchSearchResultPage
   */
  public function getOwner() {
    return $this->_owner;
  }

  /**
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->appendElement('title', array(), $this->content()->get('title', ''));
    $result = $parent->appendElement('search');
    $limit = $this->getOwner()->content()->get('limit', 10);
    $pagingRange = $this->getOwner()->content()->get('paging_range', 5);
    $offset = $this->getOwner()->papaya()->request->getParameter('offset', 0);
    $language = $this->getOwner()->papaya()->request->languageIdentifier;
    $term = trim($this->getOwner()->papaya()->request->getParameter('q', ''));

    if (!empty($term)) {

      try {
        $return = $this->searchWorker()->search($term, $language, $limit, $offset);
      } catch (PapayaModuleElasticsearchException $e) {
        $result->appendElement(
            'results',
            ['found' => 'false', 'term' => $term]
        );
        $e->appendTo($result->appendElement('error'));
        return;
      }

      if (isset($return->hits) && isset($return->hits->total) && $return->hits->total > 0) {

        $this->results()->append($result, $return, $term, $offset);

        $this->paging()->prepare($return->hits->total, $offset, $limit, $term, $pagingRange);
        $this->paging()->append($result);

      } else {
        $result->appendElement(
            'results',
            ['found' => 'false', 'term' => $term]
        );
        $result->appendElement(
            'message',
            [],
            'Nothing found.'
        );
      }
    }
  }

  /**
   * @param PapayaXmlElement $parent
   */
  public function appendQuoteTo(PapayaXmlElement $parent) {
    // Implement!
  }

  /**
   * @param PapayaModuleElasticsearchSearchWorker $searchWorker
   * @return PapayaModuleElasticsearchSearchWorker
   */
  public function searchWorker(PapayaModuleElasticsearchSearchWorker $searchWorker = NULL) {
    if (isset($searchWorker)) {
      $this->_searchWorker = $searchWorker;
    } else if (is_null($this->_searchWorker)) {
      $this->_searchWorker = new PapayaModuleElasticsearchSearchWorker();
      $this->_searchWorker->papaya($this->getOwner()->papaya());
    }
    return $this->_searchWorker;
  }

  /**
   * @param PapayaModuleElasticsearchSearchResultPageContentPaging $paging
   * @return PapayaModuleElasticsearchSearchResultPageContentPaging
   */
  public function paging(PapayaModuleElasticsearchSearchResultPageContentPaging $paging = NULL) {
    if (isset($paging)) {
      $this->_paging = $paging;
    } else if (is_null($this->_paging)) {
      $this->_paging = new PapayaModuleElasticsearchSearchResultPageContentPaging();
      $this->_paging->papaya($this->getOwner()->papaya());
    }
    return $this->_paging;
  }

  /**
   * @param PapayaModuleElasticsearchSearchResultPageContentResults $results
   * @return PapayaModuleElasticsearchSearchResultPageContentResults
   */
  public function results(PapayaModuleElasticsearchSearchResultPageContentResults $results = NULL) {
    if (isset($results)) {
      $this->_results = $results;
    } else if (is_null($this->_results)) {
      $this->_results = new PapayaModuleElasticsearchSearchResultPageContentResults();
    }
    return $this->_results;
  }
}