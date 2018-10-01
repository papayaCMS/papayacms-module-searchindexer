<?php
/**
 * Search Indexer Database Access
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
 * Search Indexer Database Access class
 *
 * Collects all database operations for the search indexer.
 *
 * @package Papaya-Modules
 * @subpackage Elasticsearch
 */
class PapayaModuleElasticsearchDatabaseAccess extends PapayaDatabaseObject {
  /**
   * Languages
   * @var array
   */
  private $_languages = [];

  /**
   * Total number of page translations to index
   * @var int
   */
  private $_total = 0;

  /**
   * Get a list of page publications and their available languages
   *
   * @return array
   */
  public function getPublicPages() {
    $result = [];
    $sql = "SELECT t.topic_id, tt.lng_id
              FROM %s t
             INNER JOIN %s tt
                ON t.topic_id = tt.topic_id
             WHERE t.published_from <= %d
             ORDER BY t.topic_id, tt.lng_id";
    $parameters = [
      $this->databaseGetTableName('topic_public'),
      $this->databaseGetTableName('topic_public_trans'),
      time()
    ];
    $this->_total = 0;
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->_total++;
        if (!isset($result[$row['topic_id']])) {
          $result[$row['topic_id']] = [];
        }
        $result[$row['topic_id']][] = $row['lng_id'];
      }
    }
    return $result;
  }

  /**
   * Get a list of indexed pages
   *
   * @param int $timestamp optional, default NULL
   * @return array
   */
  public function getIndexedPages($timestamp = NULL) {
    $result = [];
    $sql = "SELECT topic_id, language_id, indexed
              FROM %s
             WHERE status = 'success' ";
    if ($timestamp !== NULL) {
      $sql .= "AND indexed >= $timestamp";
    }
    $sql .= " ORDER BY topic_id, language_id";
    $parameters = [
      $this->databaseGetTableName('search_indexer_status')
    ];
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!isset($result[$row['topic_id']])) {
          $result[$row['topic_id']] = [];
        }
        $result[$row['topic_id']][$row['language_id']] = $row['indexed'];
      }
    }
    return $result;
  }

  /**
   * Get a list of pages with index errors
   *
   * @param int $timestamp optional, default NULL
   * @return array
   */
  public function getErrorPages($timestamp = NULL) {
    $result = [];
    $sql = "SELECT topic_id, language_id
              FROM %s
             WHERE status IN ('error', 'duplicate', 'no-content')";
    if ($timestamp !== NULL) {
      $sql .= " AND indexed >= $timestamp";
    }
    $parameters = [
      $this->databaseGetTableName('search_indexer_status')
    ];
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (!isset($result[$row['topic_id']])) {
          $result[$row['topic_id']] = [];
        }
        $result[$row['topic_id']][] = $row['language_id'];
      }
    }
    return $result;
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
    $this->databaseDeleteRecord(
      $this->databaseGetTableName('search_indexer_status'),
      ['topic_id' => $topicId, 'language_id' => $languageId]
    );
    $this->databaseInsertRecord(
      $this->databaseGetTableName('search_indexer_status'),
      NULL,
      [
        'topic_id' => $topicId,
        'language_id' => $languageId,
        'search_item_id' => $searchItemId,
        'indexed' => time(),
        'status' => $status,
        'comment' => $comment,
        'url' => $url,
        'digest' => $digest
      ]
    );
  }

  /**
   * Get index statuses by URL or digest
   *
   * @param string $url
   * @param string $digest
   * @return array
   */
  public function getIndexStatusesByUrlOrDigest($url, $digest) {
    $result = [];
    $sql = "SELECT topic_id, language_id, search_item_id,
                   indexed, status, comment, url, digest
              FROM %s
             WHERE (url = '%s'
                OR digest = '%s')
               AND status = 'success'";
    $parameters = [
      $this->databaseGetTableName('search_indexer_status'),
      $url,
      $digest
    ];
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['topic_id']] = $row;
      }
    }
    return $result;
  }

  /**
   * Set redirection
   *
   * @param integer $sourceTopicId
   * @param integer $targetTopicId
   * @return boolean
   */
  public function setRedirection($sourceTopicId, $targetTopicId) {
    $sql = "SELECT COUNT(*) num
              FROM %s
             WHERE redirection_topic_id = %d
               AND redirection_target_id = %d";
    $parameters = [
      $this->databaseGetTableName('search_indexer_redirects'),
      $sourceTopicId,
      $targetTopicId
    ];
    $exists = 0;
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $exists = $row['num'];
      }
    }
    $time = time();
    if ($exists > 0) {
      return FALSE !== $this->databaseUpdateRecord(
        $this->databaseGetTableName('search_indexer_redirects'),
        ['index_timestamp' => $time],
        [
          'redirection_topic_id' => (int)$sourceTopicId,
          'redirection_target_id' => (int)$targetTopicId
        ]
      );
    } else {
      return FALSE !== $this->databaseInsertRecord(
        $this->databaseGetTableName('search_indexer_redirects'),
        NULL,
        [
          'redirection_topic_id' => (int)$sourceTopicId,
          'redirection_target_id' => (int)$targetTopicId,
          'index_timestamp' => $time
        ]
      );
    }
  }

  /**
   * Get all topic IDs that redirect to a specific target
   *
   * @param integer $targetTopicId
   * @return array
   */
  public function getRedirections($targetTopicId) {
    $result = [];
    $sql = "SELECT redirection_topic_id, redirection_target_id
              FROM %s
             WHERE redirection_target_id = %d";
    $parameters = [
      $this->databaseGetTableName('search_indexer_redirects'),
      $targetTopicId
    ];
    if ($res = $this->databaseQueryFmt($sql, $parameters)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[] = $row['redirection_topic_id'];
      }
    }
    return $result;
  }
  /**
   * Get total number of page translations
   *
   * @return int
   */
  public function total() {
    return $this->_total;
  }
}
