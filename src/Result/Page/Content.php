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
 * @subpackage SearchIndexer
 * @version $Id: Api.php 39861 2014-06-27 09:38:58Z kersken $
 */

/**
 * Search Indexer Result Page Content class
 *
 * The result page content class does the actual querying and result displaying.
 *
 * @package Papaya-Modules
 * @subpackage SearchIndexer
 */
class PapayaModuleSearchIndexerResultPageContent {
  /**
   * @var PapayaModuleSearchIndexerResultPage
   */
  private $_owner = NULL;
  private $searchConnector = NULL;
  private $paging = NULL;
  private $results = NULL;

  /**
   * @param PapayaModuleSearchIndexerResultPage $owner
   */
  public function __construct(PapayaModuleSearchIndexerResultPage $owner) {
    $this->_owner = $owner;
  }

  /**
   * @return PapayaPluginEditableContent
   */
  public function content() {
    return $this->getOwner()->content();
  }

  /**
   * @return PapayaModuleSearchIndexerResultPage
   */
  public function getOwner() {
    return $this->_owner;
  }

  /**
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
    $result = $parent->appendElement('search');
    $limit = $this->getOwner()->content()->get('limit', 10);
    $pagingRange = $this->getOwner()->content()->get('paging_range', 5);
    $offset = $this->getOwner()->papaya()->request->getParameter('offset', 0);
    $language = $this->getOwner()->papaya()->request->languageIdentifier;
    $term = trim($this->getOwner()->papaya()->request->getParameter('q', ''));

    if (!empty($term)) {

      try {
        $return = $this->searchConnector()->search($term, $language, $limit, $offset);
      } catch (PapayaModuleSearchIndexerConnectorException $e) {
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
   * @param PapayaModuleSearchIndexerConnectorSearch $searchConnector
   * @return PapayaModuleSearchIndexerConnectorSearch
   */
  public function searchConnector(PapayaModuleSearchIndexerConnectorSearch $searchConnector = NULL) {
    if (isset($searchConnector)) {
      $this->searchConnector = $searchConnector;
    } else if (is_null($this->searchConnector)) {
      $this->searchConnector = new PapayaModuleSearchIndexerConnectorSearch();
      $this->searchConnector->papaya($this->getOwner()->papaya());
    }
    return $this->searchConnector;
  }

  /**
   * @param PapayaModuleSearchIndexerResultPageContentPaging $paging
   * @return PapayaModuleSearchIndexerResultPageContentPaging
   */
  public function paging(PapayaModuleSearchIndexerResultPageContentPaging $paging = NULL) {
    if (isset($paging)) {
      $this->paging = $paging;
    } else if (is_null($this->paging)) {
      $this->paging = new PapayaModuleSearchIndexerResultPageContentPaging();
      $this->paging->papaya($this->getOwner()->papaya());
    }
    return $this->paging;
  }

  /**
   * @param PapayaModuleSearchIndexerResultPageContentResults $results
   * @return PapayaModuleSearchIndexerResultPageContentResults
   */
  public function results(PapayaModuleSearchIndexerResultPageContentResults $results = NULL) {
    if (isset($results)) {
      $this->results = $results;
    } else if (is_null($this->results)) {
      $this->results = new PapayaModuleSearchIndexerResultPageContentResults();
    }
    return $this->results;
  }
}