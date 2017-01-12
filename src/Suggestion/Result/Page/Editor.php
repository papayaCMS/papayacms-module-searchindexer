<?php
/**
 * Created by PhpStorm.
 * User: faber
 * Date: 11.01.17
 * Time: 17:16
 */

class PapayaModuleElasticsearchSuggestionResultPageEditor extends PapayaPluginEditor {
  /**
   * @var PapayaUiControlCommandController
   */
  private $_commands = NULL;

  /**
   * (non-PHPdoc)
   * @see PapayaXmlAppendable::appendTo()
   */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->append($this->commands());
  }

  /**
   * Defines the editor commandos
   * @param PapayaUiControlCommandController $commands
   * @return PapayaUiControlCommandController
   */
  public function commands(PapayaUiControlCommandController $commands = NULL) {
    if (isset($commands)) {
      $this->_commands = $commands;
    } elseif (is_null($this->_commands)) {
      $pageContent = $this->getContent();
      $this->_commands = new PapayaUiControlCommandController('cmd', 'edit');
      $this->_commands->owner($this);
      $this->_commands['edit'] = new PapayaModuleElasticsearchSuggestionResultPageEditorController(
          $pageContent,
          $this->context()
      );
    }
    return $this->_commands;
  }
}