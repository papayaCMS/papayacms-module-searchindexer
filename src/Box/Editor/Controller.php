<?php
/**
 * Created by PhpStorm.
 * User: kersken
 * Date: 17.10.16
 * Time: 17:47
 */

class PapayaModuleElasticsearchBoxEditorController extends PapayaUiControlCommandDialog {
  /**
   * @var PapayaPluginEditableContent
   */
  private $_content = NULL;

  /**
   * @var PapayaPluginEditable
   */
  private $_context = NULL;

  /**
   * @var PapayaUiDialog
   */
  private $_dialog = NULL;

  /**
   *
   * @param PapayaPluginEditableContent $content
   * @param PapayaRequestParameters $context
   */
  public function __construct(
    PapayaPluginEditableContent $content, PapayaRequestParameters $context
  ) {
    $this->_content = $content;
    $this->_context = $context;
  }

  /**
   * Return the attached plugin content.
   *
   * @return PapayaPluginEditableContent
   */
  public function getContent() {
    return $this->_content;
  }

  /**
   *
   * @param PapayaRequestParameters $context
   * @return PapayaRequestParameters
   */
  public function context(PapayaRequestParameters $context = NULL) {
    if (isset($context)) {
      $this->_context = $context;
    }
    return $this->_context;
  }

  /**
   * (non-PHPdoc)
   * @see PapayaXmlAppendable::appendTo()
   */
  public function appendTo(PapayaXmlElement $parent) {
    if ($this->dialog()->execute()) {
      $this->getContent()->merge($this->dialog()->data());
    } elseif ($this->dialog()->isSubmitted()) {
      $this->papaya()->messages->dispatch(
        new PapayaMessageDisplayTranslated(
          PapayaMessage::SEVERITY_ERROR,
          'Invalid input. Please check the field(s) "%s".',
          array(implode(', ', $this->dialog()->errors()->getSourceCaptions()))
        )
      );
    }

    $parent->append($this->dialog());
  }

  /**
   *
   * @param PapayaUiDialog $dialog
   * @return PapayaUiDialog
   */
  public function dialog(PapayaUiDialog $dialog = NULL) {
    if (isset($dialog)) {
      $this->_dialog = $dialog;
      $dialog->data()->merge($this->getContent());
    } elseif (is_null($this->_dialog)) {
      $this->_dialog = $this->createDialog();
    }
    return $this->_dialog;
  }

  /**
   * @return PapayaUiDialog
   */
  public function createDialog() {

    $dialogCaption = 'Edit';
    $buttonCaption = 'Save';

    $dialog = new PapayaUiDialog();
    $language = $this->papaya()->administrationLanguage->getCurrent();
    $dialog->caption = new PapayaUiStringTranslated($dialogCaption);
    $dialog->image = './pics/language/'.$language['image'];
    $dialog->options->topButtons = TRUE;

    $dialog->hiddenValues()->merge(
      array(
        $this->parameterGroup() => array(
          'cmd' => 'edit'
        )
      )
    );

    $dialog->parameterGroup($this->parameterGroup());
    $dialog->data()->merge($this->getContent());
    $dialog->hiddenValues->merge($this->_context);

    $dialog->fields[] = $field = new PapayaUiDialogFieldInputPage(
      new PapayaUiStringTranslated('Result page'), 'page_id', NULL, TRUE
    );
    $dialog->fields[] = $field = new PapayaUiDialogFieldInputPage(
        new PapayaUiStringTranslated('Suggest page'), 'suggest_page_id', NULL, TRUE
    );
    $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
      new PapayaUiStringTranslated('Search term caption'),
      'caption_search_term',
      100,
      'Search term',
      new PapayaFilterText()
    );
    $dialog->fields[] = $field = new PapayaUiDialogFieldInput(
      new PapayaUiStringTranslated('Submit button caption'),
      'caption_submit',
      100,
      'Search',
      new PapayaFilterText()
    );

    $dialog->buttons[] = new PapayaUiDialogButtonSubmit(
      new PapayaUiStringTranslated($buttonCaption)
    );
    return $dialog;
  }
}