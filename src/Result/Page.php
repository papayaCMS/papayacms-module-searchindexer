<?php
/**
 * Search Indexer Result Page
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
 * Search Indexer Result Page class
 *
 * The result page class displays results of a query to the search engine.
 *
 * @package Papaya-Modules
 * @subpackage SearchIndexer
 */
class PapayaModuleSearchIndexerResultPage extends PapayaObjectInteractive
  implements
  PapayaPluginAppendable,
  PapayaPluginQuoteable,
  PapayaPluginEditable,
  PapayaPluginCacheable {
  /**
   * @var PapayaPluginEditableContent
   */
  private $_content = NULL;

  /**
   * @var PapayaModuleSearchIndexerResultPageContent
   */
  private $_pageContent = NULL;

  /**
   * @var PapayaCacheIdentifierDefinition
   */
  private $_cacheDefinition = NULL;

  /**
   * Create dom node structure of the given object and append it to the given xml
   * element node.
   *
   * @param PapayaXmlElement $parent
   */
  function appendTo(PapayaXmlElement $parent) {
    return $this->pageContent()->appendTo($parent);
  }

  /**
   * Define the code definition parameters for the output.
   *
   * @see PapayaPluginCacheable::cacheable()
   * @param PapayaCacheIdentifierDefinition $definition
   * @return PapayaCacheIdentifierDefinition
   */
  public function cacheable(PapayaCacheIdentifierDefinition $definition = NULL) {
    if (isset($definition)) {
      $this->_cacheDefinition = $definition;
    } elseif (NULL == $this->_cacheDefinition) {
      $this->_cacheDefinition = new PapayaCacheIdentifierDefinitionGroup(
        new PapayaCacheIdentifierDefinitionPage(),
        new PapayaCacheIdentifierDefinitionParameters(['q', 'offset'])
      );
    }
    return $this->_cacheDefinition;
  }

  /**
   * Getter/Setter for the content.
   *
   * @param PapayaPluginEditableContent $content
   * @return PapayaPluginEditableContent
   */
  function content(PapayaPluginEditableContent $content = NULL) {
    if ($content !== NULL) {
      $this->_content = $content;
    } elseif ($this->_content === NULL) {
      $this->_content = new PapayaPluginEditableContent();
      $this->_content->callbacks()->onCreateEditor = array($this, 'createEditor');
    }
    return $this->_content;
  }

  /**
   * Append short content (aka "quote") to the parent xml element.
   *
   * @param PapayaXmlElement $parent
   * @return NULL|PapayaXmlElement
   */
  function appendQuoteTo(PapayaXmlElement $parent) {
    return $this->appendQuoteTo($parent);
  }

  /**
   *
   * @param PapayaModuleSearchIndexerResultPageContent $pageContent
   * @return PapayaModuleSearchIndexerResultPageContent
   */
  public function pageContent(PapayaModuleSearchIndexerResultPageContent $pageContent = NULL) {
    if (isset($pageContent)) {
      $this->_pageContent = $pageContent;
    } elseif (is_null($this->_pageContent)) {
      $this->_pageContent = new PapayaModuleSearchIndexerResultPageContent($this);
    }
    return $this->_pageContent;
  }

  /**
   * @see PapayaPluginEditable::editor()
   */
  public function createEditor($callbackContext, PapayaPluginEditableContent $content = NULL) {
    $editor = new PapayaModuleSearchIndexerResultPageEditor($content);
    $editor->papaya($this->papaya());
    return $editor;
  }
}