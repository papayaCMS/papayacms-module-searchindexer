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
        $return = $this->suggestionWorker()->suggest(
            $term,
            $language
        );
        $return = $this->prepareResults($return->aggregations->autocomplete->buckets);
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

  public function prepareResults($bucketResults) {

    $newBucketsResultsOneWord = [];

    foreach ($bucketResults as $oneResult) {
      if (count(explode(" ", $oneResult->key)) == 1) {
        array_push($newBucketsResultsOneWord, $oneResult->key);
      }
    }

    $newBucketsResults = [];

    foreach ($bucketResults as $oneResult) {
      if (count($newBucketsResults) == 0) {
        array_push($newBucketsResults, $oneResult->key);
      } else {
        $match = false;
        foreach ($newBucketsResults as $key => $oneElement) {
          if (strpos($oneResult->key, $oneElement) !== FALSE && strlen($oneResult->key) > strlen($oneElement)) {
            $newBucketsResults[$key] = $oneResult->key;
            $match = true;
            break;
          }
        }
        if ($match == false) {
          array_push($newBucketsResults, $oneResult->key);
        }
      }
    }

    $results = $newBucketsResultsOneWord;

    foreach ($newBucketsResults as $oneResult) {
      if ($this->inArray($newBucketsResultsOneWord, $oneResult) == false) {
        array_push($results, $oneResult);
      }
    }

    $resultLimited = [];
    $counter = 0;
    foreach ($results as $oneResult) {
      if ($this->getOwner()->content()->get('prepared_limit', 0) > $counter++) {
        array_push($resultLimited, $oneResult);
      }
    }

    return $resultLimited;
  }

  public function inArray ($elements, $value) {
    for ($i = 0; $i < count($elements); $i++) {
      if (strtolower($elements[$i]) == strtolower($value)) {
        return true;
      }
    }
    return false;
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