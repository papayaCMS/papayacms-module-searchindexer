<?php
require_once(__DIR__.'/bootstrap.php');
class PapayaModuleSearchIndexerConnectorTest extends PapayaTestCase {
  /**
   * @covers PapayaModuleSearchIndexer::onPublishPage
   */
  public function testOnPublishPage() {
    $connector = new PapayaModuleSearchIndexerConnector();
    $worker = $this
      ->getMockBuilder('PapayaModuleSearchIndexerWorker')
      ->getMock();
    $worker
      ->expects($this->once())
      ->method('onPublishPage')
      ->will($this->returnValue(TRUE));
    $connector->worker($worker);
    $this->assertTrue($connector->onPublishPage([]));
  }

  /**
   * @covers PapayaModuleSearchIndexer::worker
   */
  public function testWorkerSet() {
    $connector = new PapayaModuleSearchIndexerConnector();
    $worker = $this
      ->getMockBuilder('PapayaModuleSearchIndexerWorker')
      ->getMock();
    $this->assertSame($worker, $connector->worker($worker));
  }

  /**
   * @covers PapayaModuleSearchIndexer::worker
   */
  public function testWorkerGet() {
    $connector = new PapayaModuleSearchIndexerConnector();
    $this->assertInstanceOf('PapayaModuleSearchIndexerWorker', $connector->worker());
  }
}