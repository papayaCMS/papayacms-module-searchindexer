<?php

class PapayaModuleElasticsearchSearchResultPageContentResults {

  public function append(PapayaXmlElement $node, $return, $term, $offset) {

    $results = $node->appendElement(
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

      $oneResult = $results->appendElement(
          'result',
          [
              'id' => $hit->_id,
              'url' => $hit->_source->url,
              'title' => $hit->_source->title,
              'score' => $hit->_score
          ]
      );

      if (
          isset($hit->highlight)
          && isset($hit->highlight->content)
          && count($hit->highlight->content) > 0
      ) {
        $document = new \Papaya\XML\Document();
        for ($i = 0; $i < count($hit->highlight->content); $i++) {
          try {
            $html = $hit->highlight->content[$i];
            if (trim($html) != '') {
              @$document->loadHTML('<?xml encoding="UTF-8" ?>'.$hit->highlight->content[$i]);
              $fragment = $oneResult->appendElement('fragment');
              foreach ($document->xpath()->evaluate('//body/node()') as $node) {
                $fragment->appendChild($fragment->ownerDocument->importNode($node, TRUE));
              }
            }
          } catch (Exception $e) {
          }
        }
      } elseif (preg_match('(^(.{1,200}\b))', $hit->_source->content, $match)) {
        $content = $match[1];
        $oneResult->appendText($content);
      } else {
        $content = substr($hit->_source->content, 0, 200);
        $oneResult->appendText($content);
      }
    }
  }

  function fragmentsToXML() {

  }
}
