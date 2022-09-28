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
 * @subpackage Elasticsearch
 * @version $Id: Api.php 39861 2014-06-27 09:38:58Z kersken $
 */

/**
 * Search Indexer Worker class
 *
 * The worker class provides the actual functionality.
 *
 * @package Papaya-Modules
 * @subpackage Elasticsearch
 */
class PapayaModuleElasticsearchIndexerWorker extends PapayaObject {
  /**
   * Database access object
   * @var PapayaModuleElasticsearchDatabaseAccess
   */
  private $_databaseAccess = NULL;

  /**
   * Indexer writer object
   * @var PapayaModuleElasticsearchIndexerWriter
   */
  private $_writer = NULL;

  /**
   * Module option values
   * @var mixed
   */
  private $_moduleOptions = NULL;

  /**
   * Pages connector
   * @var PagesConnector
   */
  private $_pagesConnector = NULL;

  /**
   * Information on indexing multiple pages
   * @var array
   */
  private $_info = [
    'total' => 0,
    'attempted' => 0,
    'success' => 0,
    'errors' => 0,
    'skipped_indexed' => 0,
    'skipped_errors' => 0
  ];

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
    if ($result) {
      $redirects = $this->databaseAccess()->getRedirections($data['topic_id']);
      if ($redirects) {
        foreach ($redirects as $pageId) {
          foreach ($data['languages'] as $languageId) {
            if ($this->indexPage($pageId, $languageId)) {
              $this->databaseAccess()->setRedirection($pageId, $data['topic_id']);
            }
          }
        }
      }
    }
    return $result;
  }

  /**
   * Callback method to be called via action dispatcher whenever a specific page translation is unpublished
   */
  public function onUnpublishPage($data) {
    $result = $this->writer()->removeFromIndex(
      $data['topic_id'],
      $this->getLanguageById($data['lng_id'])
    );
    return $result;
  }

  /**
   * Callback method to be called via action dispatcher whenever a lot of pages should be removed from index
   * @param array $data
   * @return boolean
   */
  public function onDeletePages($data) {
    if (isset($data['topic_translations']) && is_array($data['topic_translations'])) {
      $status = [];
      foreach ($data['topic_translations'] as $onePage) {
        $status[] = $this->onUnpublishPage($onePage);
      }
      return in_array(FALSE, $status) ? FALSE : TRUE;
    }
    return FALSE;
  }

  /**
   * Index all pages
   *
   * @param bool $overrideIndexed optional, default value FALSE
   * @param int $minTimestamp optional, default value NULL
   * @param int $errorMinTimestamp optional, default value NULL
   */
  public function indexAllPages(
    $overrideIndexed = FALSE,
    $minTimestamp = NULL,
    $errorMinTimestamp = NULL,
    $baseUrl = ''
  ) {

    $pages = $this->databaseAccess()->getPublicPages();
    $total = $this->databaseAccess()->total();
    $attempted = 0;
    $success = 0;
    $errors = 0;
    $skippedIndexed = 0;
    $skippedErrors = 0;
    if (!($overrideIndexed || $minTimestamp === 0)) {
      $indexedPages = $this->databaseAccess()->getIndexedPages($minTimestamp);
      foreach ($indexedPages as $topicId => $data) {
        foreach ($data as $languageId => $timestamp) {
          if (isset($pages[$topicId]) && in_array($languageId, $pages[$topicId])) {
            $skippedIndexed++;
            $index = array_search($languageId, $pages[$topicId]);
            unset($pages[$topicId][$index]);
          }
        }
      }
    }

    $errorPages = $this->databaseAccess()->getErrorPages($errorMinTimestamp);
    foreach ($pages as $topicId => $languages) {
      foreach ($languages as $languageId) {
        if (isset($errorPages[$topicId]) && in_array($languageId, $errorPages[$topicId])) {
          $skippedErrors++;
          break;
        }
        if ($attempted >= 100) {
          break 2;
        }
        $attempted++;

        if ($this->indexPage($topicId, $languageId, $baseUrl)) {
          $success++;
        } else {
          $errors++;
        }
      }
    }
    $this->_info = [
      'total' => $total,
      'attempted' => $attempted,
      'success' => $success,
      'errors' => $errors,
      'skipped_indexed' => $skippedIndexed,
      'skipped_errors' => $skippedErrors
    ];
  }

  /**
   * Index a page in a specific language
   *
   * @param integer $topicId
   * @param integer $languageId
   * @param string $baseUrl
   * @return boolean
   */
  public function indexPage($topicId, $languageId, $baseUrl = '') {
    $result = FALSE;
    $identifier = $this->getLanguageById($languageId);
    $reference = Papaya\UI\Reference\Page::create(
      $baseUrl ? new Papaya\URL($baseUrl) : $this->papaya()->request->url
    );
    $reference->setOutputMode($this->option('OUTPUT_MODE', 'html'));
    $reference->setPageLanguage($identifier);
    $reference->setPageId($topicId);
    $reference->setPreview(FALSE);
    $url = $reference->get(TRUE);
    $options = [
      'http' => [
        'method' => 'POST',
        'header' => 'Connection: close'
      ]
    ];
    $context = stream_context_create($options);
    $redirectCount = 0;
    $cookiesSet = FALSE;
    $content = NULL;
    $redirectionUrls = [];
    do {
      $stream = fopen($url, 'r', FALSE, $context);
      $goOn = FALSE;
      $finalUrl = $url;
      $redirectionUrls[] = $url;
      if ($url = $this->detectRedirection($http_response_header)) {
        $redirectCount++;
        if ($redirectCount < 10) {
          if (!$cookiesSet && $cookies = $this->searchCookie($http_response_header)) {
            $cookiesSet = TRUE;
            foreach ($cookies as $cookie) {
              $options['http']['header'] .= "\r\nCookie: $cookie";
            }
            $context = stream_context_create($options);
          }
          $goOn = TRUE;
        } else {
          $this->setIndexed(
            $topicId,
            $languageId,
            $this->lastSearchItemId(),
            'error',
            'Too many redirects.'
          );
          break;
        }
      }
      if (!$url && is_resource($stream)) {
        $content = stream_get_contents($stream);
        fclose($stream);
        $titles = $this->pagesConnector()->getTitles($topicId, $languageId);
        $title = '';
        if (isset($titles[$topicId])) {
          $title = $titles[$topicId];
        }
        $content = $this->takeContent($content);
        if (is_null($content)) {
          $status = 'no-content';
          $this->setIndexed($topicId, $languageId, $topicId, $status);
        } else {
          $duplicate = FALSE;
          $contentDigest = $this->getDigest($content);
          $statuses = $this->databaseAccess()->getIndexStatusesByUrlOrDigest($finalUrl, $contentDigest);
          foreach ($statuses as $pageId => $statusData) {
            if ($topicId != $pageId && (
                  $finalUrl == $statusData['url'] ||
                  $contentDigest == $statusData['digest'])
               ) {
              $duplicate = TRUE;
              $this->setIndexed(
                $topicId,
                $languageId,
                '',
                'duplicate',
                sprintf('Duplicate of page %d.', $statusData['topic_id'])
              );
              break;
            }
          }
          if (!$duplicate) {
            $result = $this->addToIndex($topicId, $identifier, $finalUrl, $content, $title);
            $status = 'error';
            $logUrl = '';
            $logDigest = '';
            if ($result) {
              $status = 'success';
              $searchItemId = $result ? $result : $this->lastSearchItemId();
              $logUrl = $finalUrl;
              $logDigest = $contentDigest;
            }
            $this->setIndexed($topicId, $languageId, $searchItemId, $status, '', $logUrl, $logDigest);
          }
        }
        break;
      } elseif (!$goOn) {
        $this->setIndexed(
          $topicId,
          $languageId,
          $this->lastSearchItemId(),
          'error',
          'Cannot write to index.'.$finalUrl
        );
        break;
      }
    } while ($goOn);
    if (count($redirectionUrls) > 1) {
      $pageIds = [];
      foreach ($redirectionUrls as $url) {
        $request = new PapayaRequest();
        $request->load(new PapayaUrl($url));
        $pageId = $request->pageId;
        if (!in_array($pageId, $pageIds)) {
          $pageIds[] = $pageId;
        }
      }
      if (count($pageIds) > 1) {
        $this->setRedirections($pageIds);
      }
    }
    return $result;
  }

  /**
   * Retrieve the content from a content container if defined
   *
   * @param string $html
   * @return mixed|null
   */
  public function takeContent($html) {
    $id = $this->option('PAGE_CONTENT_CONTAINER', '');
    if ($id != '') {
      $errors = new \Papaya\XML\Errors();
      return $errors->encapsulate(
        static function () use ($html, $id) {
          $document = new \Papaya\XML\Document();
          if (@$document->loadHtml($html) === FALSE) {
            return NULL;
          }
          $text = $document->xpath()->evaluate('string(//*[@id="'.$id.'"])');
          return ($text != '') ? $text : NULL;
        },
        NULL,
        FALSE
      );
    }
    return $html;
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
    return $this->writer()->addToIndex($topicId, $identifier, $url, $content, $title, $itemId);
  }

  /**
   * Remove a node from the index
   *
   * @param string $nodeId
   * @param string $identifier Language identifier
   * @return bool
   */
  public function removeFromIndex($nodeId, $identifier) {
    return $this->writer()->removeFromIndex($nodeId, $identifier);
  }

  /**
   * Set a page with a specific language as indexed
   *
   * @param int $topicId
   * @param int $languageId
   * @param string $searchItemId
   * @param string $status
   * @param string $comment optional, default value ''
   * @param string $url optional, default value ''
   * @param string $digest optional, default value ''
   */
  public function setIndexed($topicId, $languageId, $searchItemId, $status,
                             $comment = '', $url = '', $digest = '') {
    return $this->databaseAccess()->setIndexed(
      $topicId,
      $languageId,
      $searchItemId,
      $status,
      $comment,
      $url,
      $digest
    );
  }

  /**
   * Detect redirection from response headers
   *
   * @param array $headers
   * @return bool|string Redirection URL or FALSE if there's no redirection
   */
  protected function detectRedirection($headers) {
    $result = FALSE;
    $status = $this->searchStatus($headers);
    if ($status >= 300 && $status <= 399) {
      $result = $this->searchLocation($headers);
    }
    return $result;
  }

  /**
   * Find HTTP status code in response headers
   *
   * @param array $headers
   * @return int Status code
   */
  protected function searchStatus($headers) {
    $result = 0;
    foreach ($headers as $header) {
      if (preg_match('(^HTTP.*(\d{3}))', $header, $matches)) {
        $result = $matches[1];
        break;
      }
    }
    return $result;
  }

  /**
   * Find contents of Location HTTP header
   *
   * @param array $headers
   * @return string location
   */
  protected function searchLocation($headers) {
    $result = '';
    foreach ($headers as $header) {
      if (preg_match('(^Location:\s*(.*))', $header, $matches)) {
        $result = $matches[1];
        break;
      }
    }
    return $result;
  }

  /**
   * Find contents of Set-Cookie HTTP headers
   *
   * @param array $headers
   * @return array|bool Array of cookies or FALSE if there aren't any
   */
  protected function searchCookie($headers) {
    $result = FALSE;
    foreach ($headers as $header) {
      if (preg_match('(^set\-cookie:\s*(.*))i', $header, $matches)) {
        if (!is_array($result)) {
          $result = [];
        }
        $result[] = $matches[1];
      }
    }
    return $result;
  }

  /**
   * Get/set/initialize the writer object
   *
   * @param PapayaModuleElasticsearchIndexerWriter $writer optional, default value NULL
   * @return PapayaModuleElasticsearchIndexerWriter
   */
  public function writer($writer = NULL) {
    if ($writer !== NULL) {
      $this->_writer = $writer;
    } elseif ($this->_writer === NULL) {
      $this->_writer = new PapayaModuleElasticsearchIndexerWriter();
      $this->_writer->owner($this);
    }
    return $this->_writer;
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
      $this->_pagesConnector = $this->papaya()->plugins->get('69db080d0bb7ce20b52b04e7192a60bf', $this);
    }
    return $this->_pagesConnector;
  }

  /**
   * Get/set/initialize the database access object
   *
   * @param PapayaModuleElasticsearchDatabaseAccess optional, default value NULL
   * @return PapayaModuleElasticsearchDatabaseAccess
   */
  public function databaseAccess($databaseAccess = NULL) {
    if ($databaseAccess !== NULL) {
      $this->_databaseAccess = $databaseAccess;
    } elseif ($this->_databaseAccess === NULL) {
      $this->_databaseAccess = new PapayaModuleElasticsearchDatabaseAccess();
    }
    return $this->_databaseAccess;
  }

  /**
   * Get a module option
   *
   * @param string $option
   * @param mixed $default optional, default value NULL
   * @return mixed
   */
  public function option($option, $default = NULL) {
    if ($this->_moduleOptions === NULL) {
      $this->_moduleOptions = $this->papaya()->plugins->options[PapayaModuleElasticsearchIndexerConnector::MODULE_GUID];
    }
    return $this->_moduleOptions->get($option, $default);
  }

  /**
   * Get information
   *
   * @return array
   */
  public function info() {
    return $this->_info;
  }

  /**
   * Get the last search item ID
   *
   * @return string
   */
  public function lastSearchItemId() {
    return $this->writer()->lastSearchItemId();
  }

  /**
   * Get a language identifier by its numeric ID
   *
   * @param int $languageId
   * @return string
   */
  protected function getLanguageById($languageId) {
    $language = new PapayaContentLanguage();
    $language->load(['id' => $languageId]);
    return $language['identifier'];
  }

  /**
   * Set redirections
   *
   * @param $pageIds
   */
  private function setRedirections($pageIds) {
    if (count($pageIds) > 1) {
      $target = array_pop($pageIds);
      foreach ($pageIds as $pageId) {
        $this->databaseAccess()->setRedirection($pageId, $target);
      }
    }
  }

  /**
   * Get a digest for a string
   *
   * @param string $text
   * @return string
   */
  private function getDigest($text) {
    return md5($text);
  }
}
