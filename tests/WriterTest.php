<?php
require_once(__DIR__.'/bootstrap.php');
require_once(__DIR__.'/../src/Indexer/Writer.php');

class PapayaModuleElasticsearchIndexerWriterTest extends PapayaTestCase {
  /**
   * @covers PapayaModuleElasticsearchIndexerWriter::owner
   */
  public function testOwner() {
    $writer = new PapayaModuleElasticsearchIndexerWriter();
    $owner = $this
      ->getMockBuilder('PapayaModuleElasticsearchIndexerWorker')
      ->getMock();
    $this->assertSame($owner, $writer->owner($owner));
  }

  /**
   * @covers PapayaModuleElasticsearchIndexerWriter::prepareContent
   */
  public function testPrepareContent() {
    $writer = new PapayaModuleElasticsearchIndexerWriter_TestProxy();
    $this->assertEquals(
      "Title Headline Line 1\nLine 2",
      $writer->prepareContent("<html><head><title>Title</title></head><body><h1>Headline</h1><p>Line 1

       Line 2</p></body></html>")
    );
  }
}

class PapayaModuleElasticsearchIndexerWriter_TestProxy extends PapayaModuleElasticsearchIndexerWriter {
  public function prepareContent($content) {
    return parent::prepareContent($content);
  }
}
