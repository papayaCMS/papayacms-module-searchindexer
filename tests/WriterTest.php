<?php
require_once(__DIR__.'/bootstrap.php');

class PapayaModuleSearchIndexerWriterTest extends PapayaTestCase {
  /**
   * @covers PapayaModuleSearchIndexerWriter::owner
   */
  public function testOwner() {
    $writer = new PapayaModuleSearchIndexerWriter();
    $owner = $this
      ->getMockBuilder('PapayaModuleSearchIndexerWorker')
      ->getMock();
    $this->assertSame($owner, $writer->owner($owner));
  }

  /**
   * @covers PapayaModuleSearchIndexerWriter::prepareContent
   */
  public function testPrepareContent() {
    $writer = new PapayaModuleSearchIndexerWriter_TestProxy();
    $this->assertEquals(
      "Title Headline Line 1\nLine 2",
      $writer->prepareContent("<html><head><title>Title</title></head><body><h1>Headline</h1><p>Line 1

       Line 2</p></body></html>")
    );
  }
}

class PapayaModuleSearchIndexerWriter_TestProxy extends PapayaModuleSearchIndexerWriter {
  public function prepareContent($content) {
    return parent::prepareContent($content);
  }
}