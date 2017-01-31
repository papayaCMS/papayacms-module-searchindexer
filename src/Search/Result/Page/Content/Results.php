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
      if (
          isset($hit->highlight)
          && isset($hit->highlight->content)
          && count($hit->highlight->content > 0)
      ) {
        $content = $hit->highlight->content[0];
      } elseif (preg_match('(^(.{1,200}\b))', $hit->_source->content, $match)) {
        $content = $match[1];
      } else {
        $content = substr($hit->_source->content, 0, 200);
      }
      $oneResult = $results->appendElement(
          'result',
          [
              'url' => $hit->_source->url,
              'title' => $hit->_source->title,
              'score' => $hit->_score
          ]
      );

      $oneResult->appendXml($content);
    }
  }
}