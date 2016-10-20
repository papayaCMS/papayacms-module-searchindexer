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
    $host = $this->getOwner()->content()->get('search_host', 'localhost');
    $port = $this->getOwner()->content()->get('search_port', 9200);
    $index = $this->getOwner()->content()->get('search_index', 'index');
    $limit = $this->getOwner()->content()->get('limit', 10);
    $offset = $this->getOwner()->papaya()->request->getParameter('offset', 0);
    $language = $this->getOwner()->papaya()->request->languageIdentifier;
    $url = sprintf("http://%s:%d/%s/%s/_search", $host, $port, $index, $language);
    $term = trim($this->getOwner()->papaya()->request->getParameter('q', ''));
    if (!empty($term)) {
      $term = preg_replace('(^\W+)u', '', $term);
      $term = preg_replace('(\W+$)u', '', $term);
      $activeTerm = $term;
      if (!preg_match('(\s)', $activeTerm)) {
        $activeTerm = sprintf('*%s*', $activeTerm);
      }
      $rawQuery = [
        'from' => $offset,
        'size' => $limit,
        'query' => [
          'query_string' => [
            'query' => $activeTerm,
            'fields' => ['title^2', 'content']
          ]
        ],
        'highlight' => [
          'fields' => [
            'content' => new stdClass()
          ]
        ]
      ];
      $query = json_encode($rawQuery);
      $options = [
        'http' => [
          'method' => 'GET',
          'header' => "Content-type: application/json\r\nContent-length: " . strlen($query),
          'content' => $query
        ]
      ];
      $context = stream_context_create($options);
      if ($connection = @fopen($url, 'r', FALSE, $context)) {
        $return = json_decode(stream_get_contents($connection));
        if (isset($return->hits) && isset($return->hits->total)) {
          if ($return->hits->total == 0) {
            $result->appendElement(
              'results',
              ['found' => 'false', 'term' => $term]
            );
            $result->appendElement(
              'error',
              [],
              'Nothing found.'
            );
          } else {
            $results = $result->appendElement(
              'results',
              [
                'found' => 'true',
                'term' => $term,
                'total' => $return->hits->total,
                'start' => $offset + 1,
                'end' => $offset + count($return->hits->hits)
              ]
            );
            foreach ($return->hits->hits as $hit) {
              if (isset($hit->highlight) &&
                isset($hit->highlight->content) && count($hit->highlight->content > 0)
              ) {
                $content = $hit->highlight->content[0];
              } elseif (preg_match('(^(.{1,200}\b))', $hit->_source->content, $match)) {
                $content = $match[1];
              } else {
                $content = substr($hit->_source->content, 0, 200);
              }
              $results->appendElement(
                'result',
                [
                  'url' => $hit->_source->url,
                  'title' => $hit->_source->title,
                  'content' => $content
                ]
              );
            }
            $this->appendPaging($result, $term, $return->hits->total, $offset, $limit);
          }
        } else {
          $result->appendElement(
            'results',
            ['found' => 'false', 'term' => $term]
          );
          $result->appendElement('error', [], 'Unexpected search result format.');
        }
      } else {
        $result->appendElement(
          'results',
          ['found' => 'false', 'term' => $term]
        );
        $result->appendElement('error', [], 'Invalid search server connection.');
      }
    }
  }

  /**
   * @param PapayaXmlElement $parent
   */
  public function appendQuoteTo(PapayaXmlElement $parent) {
    // Implement!
  }

  protected function appendPaging($node, $term, $total, $offset, $limit) {
    if ($total > $limit) {
      $language = $this->getOwner()->papaya()->request->languageIdentifier;
      $pageId = $this->getOwner()->papaya()->request->pageId;
      $paging = $node->appendElement('paging');
      for ($i = 0; $i <= floor($total / $limit) * $limit; $i += $limit) {
        $reference = $this->getOwner()->papaya()->pageReferences->get(
          $language,
          $pageId
        );
        $reference->setParameters(
          [
            'q' => $term,
            'offset' => $i
          ]
        );
        $attributes = [
          'href' => (string)$reference,
          'type' => 'page'
        ];
        if ($i == $offset) {
          $attributes['current'] = 'true';
        }
        $paging->appendElement('page', $attributes);
      }
      if ($offset > 0) {
        $reference = $this->getOwner()->papaya()->pageReferences->get(
          $language,
          $pageId
        );
        $reference->setParameters(
          [
            'q' => $term,
            'offset' => $offset - $limit
          ]
        );
        $attributes = [
          'href' => (string)$reference,
          'type' => 'previous'
        ];
        $paging->appendElement('page', $attributes);
      }
      if ($offset < floor($total / $limit) * $limit) {
        $reference = $this->getOwner()->papaya()->pageReferences->get(
          $language,
          $pageId
        );
        $reference->setParameters(
          [
            'q' => $term,
            'offset' => $offset + $limit
          ]
        );
        $attributes = [
          'href' => (string)$reference,
          'type' => 'next'
        ];
        $paging->appendElement('page', $attributes);
      }
    }
  }
}