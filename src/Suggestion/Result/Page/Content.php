<?php
/**
 * Elasticsearch Suggestion Result Page Content
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
class PapayaModuleElasticsearchSuggestionResultPageContent {
  /**
   * @var PapayaModuleElasticsearchSuggestionResultPage
   */
  private $_owner = NULL;
  private $_searchWorker = NULL;

  /**
   * @param PapayaModuleElasticsearchSuggestionResultPage $owner
   */
  public function __construct(PapayaModuleElasticsearchSuggestionResultPage $owner) {
    $this->_owner = $owner;
  }

  /**
   * @return PapayaPluginEditableContent
   */
  public function content() {
    return $this->getOwner()->content();
  }

  /**
   * @return PapayaModuleElasticsearchSuggestionResultPage
   */
  public function getOwner() {
    return $this->_owner;
  }

  /**
   * @param PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
    $result = $parent->appendElement('suggestion');
    $language = $this->getOwner()->papaya()->request->languageIdentifier;
    $term = trim($this->getOwner()->papaya()->request->getParameter('term', ''));

    if (!empty($term)) {

      try {
        $return = $this->suggestionWorker()->suggest($term, $language);
      } catch (PapayaModuleElasticsearchException $e) {
        $result->appendElement(
            'results',
            ['found' => 'false', 'term' => $term]
        );
        $e->appendTo($result->appendElement('error'));
        return;
      }

      $result->appendElement(
          'results',
          ['found' => 'true', 'term' => $term, 'content' => json_encode($return)]
      );
    }
  }

  /**
   * @param PapayaXmlElement $parent
   */
  public function appendQuoteTo(PapayaXmlElement $parent) {
    // Implement!
  }

  /**
   * @param PapayaModuleElasticsearchSuggestionWorker $searchWorker
   * @return PapayaModuleElasticsearchSuggestionWorker
   */
  public function suggestionWorker(PapayaModuleElasticsearchSuggestionWorker $searchWorker = NULL) {
    if (isset($searchWorker)) {
      $this->_searchWorker = $searchWorker;
    } else if (is_null($this->_searchWorker)) {
      $this->_searchWorker = new PapayaModuleElasticsearchSuggestionWorker();
      $this->_searchWorker->papaya($this->getOwner()->papaya());
    }
    return $this->_searchWorker;
  }


}