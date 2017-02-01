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
        for ($i = 0; $i < count($hit->highlight->content); $i++) {
          $fragment = $oneResult->appendElement('fragment');
          $fragment->appendXml($hit->highlight->content[$i]);
        }
      } elseif (preg_match('(^(.{1,200}\b))', $hit->_source->content, $match)) {
        $content = $match[1];
        $oneResult->appendXml($content);
      } else {
        $content = substr($hit->_source->content, 0, 200);
        $oneResult->appendXml($content);
      }
    }
  }
}