<?php

class PapayaModuleElasticsearchIndexerWorkerPage {

  public $url = '';
  public $content = '';
  public $isIndexable = FALSE;
  public $isValid = FALSE;

  public function __construct($url, $html, $contentMarker) {
    $this->url = $url;
    $errors = new \Papaya\XML\Errors();
    $this->isValid = $errors->encapsulate(
      function () use ($html, $contentMarker) {
        $document = new \Papaya\XML\Document();
        if (@$document->loadHtml($html) === FALSE) {
          return FALSE;
        }
        $this->isIndexable = $document->xpath()->evaluate(
          'not(contains(//meta[@name="robots"]/@content, "noindex"))'
        );
        $this->title = $this->fetchAlternative(
          $document,
          [
            'string(//body//h1)',
            'string(//head//title)',
          ]
        );
        $this->content = $this->fetchAlternative(
          $document,
          [
            'string(//*[@id="'.$contentMarker.'"])',
            'string(//*[contains(concat(" ", normalize-space(@class), " "), " content ")])',
            'string(//body)',
          ]
        );
        return TRUE;
      },
      NULL,
      FALSE
    );
  }

  private function fetchAlternative(\Papaya\XML\Document $document, array $expressions) {
    return array_reduce(
      $expressions,
      function ($carry, $expression) use ($document) {
        return empty($carry)
          ? $document->xpath()->evaluate($expression)
          : $carry;
      },
      ''
    );
  }
}
