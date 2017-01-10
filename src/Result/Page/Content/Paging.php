<?php

class PapayaModuleSearchIndexerResultPageContentPaging {

  /**
   * @var int
   */
  private $total = 0;
  /**
   * @var int
   */
  private $offset = 0;
  /**
   * @var int
   */
  private $limit = 10;

  private $pagingRange = 3;
  /**
   * @var string
   */
  private $term = '';

  private $papaya = NULL;

  public function prepare(
      $total,
      $offset,
      $limit,
      $term,
      $pagingRange

  ) {
    $this->total = $total;
    $this->offset = $offset;
    $this->limit = $limit;
    $this->term = $term;
    $this->pagingRange = $pagingRange;
  }

  public function append($node) {
    $pagingNode = $this->appendPages($node);
    $this->appendFirstLink($pagingNode);
    $this->appendPreviousLink($pagingNode);
    $this->appendNextLink($pagingNode);
    $this->appendLastLink($pagingNode);
  }

  public function appendPages(PapayaXmlElement $node) {
    if ($this->total > $this->limit) {
      $language = $this->papaya()->request->languageIdentifier;
      $pageId = $this->papaya()->request->pageId;


      $start = $this->offset - ($this->pagingRange * $this->limit);
      $end = $this->offset + ($this->pagingRange * $this->limit) ;

      if ($start <= 0) {
        $start = 0;
        $end = $start + ($this->limit * 2 * $this->pagingRange);
      }

      if ($end >= (floor($this->total / $this->limit) * $this->limit)) {
        $end = floor($this->total / $this->limit) * $this->limit;
        $start = $end - ($this->pagingRange * $this->limit *  2);
        if ($start < 0) {
          $start = 0;
        }
      }

      $paging = $node->appendElement(
          'paging',
          array(
              'total' => floor($this->total / $this->limit) + 1,
              'start-number' => $start/$this->limit + 1,
              'end-number' => $end/$this->limit + 1,
              'start-offset' => $start,
              'end-offset' => $end
          )
      );

      for ($i = $start; $i <= $end; $i += $this->limit) {
        $reference = $this->papaya()->pageReferences->get(
            $language,
            $pageId
        );
        $reference->setParameters(
            [
                'q' => $this->term,
                'offset' => $i
            ]
        );
        $attributes = [
            'href' => (string)$reference,
            'type' => 'page',
            'number' => $i / $this->limit + 1
        ];
        if ($i == $this->offset) {
          $attributes['current'] = 'true';
        }
        $paging->appendElement('page', $attributes);
      }
      return $paging;
    }
    return $node;
  }

  public function appendFirstLink(PapayaXmlElement $node) {
    $language = $this->papaya()->request->languageIdentifier;
    $pageId = $this->papaya()->request->pageId;

    if ($this->offset > $this->limit) {
      $referenceFirst = $this->papaya()->pageReferences->get(
          $language,
          $pageId
      );
      $referenceFirst->setParameters(
          [
              'q' => $this->term,
              'offset' => 0
          ]
      );
      $attributes = [
          'href' => (string)$referenceFirst,
          'type' => 'first'
      ];
      $node->appendElement('page', $attributes);
    }

  }

  public function appendPreviousLink($node) {
    $language = $this->papaya()->request->languageIdentifier;
    $pageId = $this->papaya()->request->pageId;

    if ($this->offset > 0) {
      $referencePrevious = $this->papaya()->pageReferences->get(
          $language,
          $pageId
      );
      $referencePrevious->setParameters(
          [
              'q' => $this->term,
              'offset' => $this->offset - $this->limit
          ]
      );
      $attributes = [
          'href' => (string)$referencePrevious,
          'type' => 'previous'
      ];
      $node->appendElement('page', $attributes);
    }
  }

  public function appendNextLink($node) {

    $language = $this->papaya()->request->languageIdentifier;
    $pageId = $this->papaya()->request->pageId;

    if ($this->offset < floor($this->total / $this->limit) * $this->limit) {
      $referenceNext = $this->papaya()->pageReferences->get(
          $language,
          $pageId
      );
      $referenceNext->setParameters(
          [
              'q' => $this->term,
              'offset' => $this->offset + $this->limit
          ]
      );
      $attributes = [
          'href' => (string)$referenceNext,
          'type' => 'next'
      ];
      $node->appendElement('page', $attributes);
    }
  }

  public function appendLastLink($node) {

    $language = $this->papaya()->request->languageIdentifier;
    $pageId = $this->papaya()->request->pageId;

    if ($this->offset < floor($this->total / $this->limit) * $this->limit - $this->limit) {
      $referenceLast = $this->papaya()->pageReferences->get(
          $language,
          $pageId
      );
      $referenceLast->setParameters(
          [
              'q' => $this->term,
              'offset' => floor($this->total / $this->limit) * $this->limit
          ]
      );
      $attributes = [
          'href' => (string)$referenceLast,
          'type' => 'last'
      ];
      $node->appendElement('page', $attributes);
    }
  }

  /**
   * @param PapayaApplication $papaya
   * @return PapayaApplication
   */
  public function papaya(PapayaApplication $papaya = NULL) {
    if (isset($papaya)) {
      $this->papaya = $papaya;
    } else if (is_null($this->papaya)) {
      $this->papaya = new PapayaApplication();
    }
    return $this->papaya;
  }
}