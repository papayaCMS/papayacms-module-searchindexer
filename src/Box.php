<?php
/**
* Elasticsearch Box
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
* Search Indexer Box class
*
* The box class displays a simple search form.
*
* @package Papaya-Modules
* @subpackage Elasticsearch
*/
class PapayaModuleElasticsearchBox
extends
  PapayaObjectInteractive
implements
  PapayaPluginAppendable,
  PapayaPluginEditable,
  PapayaPluginCacheable {
  /**
   * @var PapayaPluginEditableContent
   */
  private $_content = NULL;

  private $_cacheDefinition = NULL;

  /**
   * Append the page output xml to the DOM.
   *
   * @see PapayaXmlAppendable::appendTo()
   */
  public function appendTo(PapayaXmlElement $parent) {
    $dialog = new PapayaUiDialog($this);
    $reference = $this->papaya()->pageReferences->get(
      $this->papaya()->request->languageIdentifier,
      $this->content()->get('page_id')
    );
    $dialog->action($reference);
    $dialog->parameterMethod(PapayaUiDialog::METHOD_GET);
    $dialog->options->useToken = FALSE;
    $dialog->options->useConfirmation = FALSE;
    $dialog->fields[] = new PapayaUiDialogFieldInput(
      $this->content()->get('caption_search_term', 'Search term'),
      'q',
      100,
      NULL,
      new PapayaFilterText()
    );
    $dialog->buttons[] = new PapayaUiDialogButtonSubmit(
      $this->content()->get('caption_submit', 'Submit')
    );
    $parent->append($dialog);
  }

  /**
   * The content is an {@see ArrayObject} containing the stored data.
   *
   * @see PapayaPluginEditable::content()
   * @param PapayaPluginEditableContent $content
   * @return PapayaPluginEditableContent
   */
  public function content(PapayaPluginEditableContent $content = NULL) {
    if (isset($content)) {
      $this->_content = $content;
    } elseif (is_null($this->_content)) {
      $this->_content = new PapayaPluginEditableContent();
      $this->_content->callbacks()->onCreateEditor = array($this, 'createEditor');
    }
    return $this->_content;
  }

  /**
   * The editor is used to change the stored data in the administration interface.
   *
   * In this case the editor creates an dialog from a field definition.
   *
   * @see PapayaPluginEditable::content()
   *
   * @param object $callbackContext
   * @param PapayaPluginEditableContent $content
   * @internal param \PapayaPluginEditor $editor
   * @return PapayaPluginEditor
   */
  public function createEditor($callbackContext, PapayaPluginEditableContent $content) {
    $editor = new PapayaModuleElasticsearchBoxEditor($content);
    $editor->papaya($this->papaya());
    return $editor;
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
        new PapayaCacheIdentifierDefinitionUrl()
      );
    }
    return $this->_cacheDefinition;
  }
}