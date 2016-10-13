<?php
require_once(__DIR__.'/bootstrap.php');
class PapayaModuleSearchIndexerConnectorTest extends PapayaTestCase {
  /**
   * @covers PapayaModuleSearchIndexerConnector::onPublishPage
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
   * @covers PapayaModuleSearchIndexerConnector::worker
   */
  public function testWorkerSet() {
    $connector = new PapayaModuleSearchIndexerConnector();
    $worker = $this
      ->getMockBuilder('PapayaModuleSearchIndexerWorker')
      ->getMock();
    $this->assertSame($worker, $connector->worker($worker));
  }

  /**
   * @covers PapayaModuleSearchIndexerConnector::worker
   */
  public function testWorkerGet() {
    $connector = new PapayaModuleSearchIndexerConnector();
    $this->assertInstanceOf('PapayaModuleSearchIndexerWorker', $connector->worker());
  }

  /**
   * @covers PapayaModuleSearchIndexerConnector::option
   */
  public function testOption() {
    $connector = new PapayaModuleSearchIndexerConnector();
    $worker = $this
      ->getMockBuilder('PapayaModuleSearchIndexerWorker')
      ->getMock();
    $worker
      ->expects($this->once())
      ->method('option')
      ->will($this->returnValue('searchindex'));
    $connector->worker($worker);
    $this->assertEquals('searchindex', $connector->option('ELASTICSEARCH_INDEX', 'index'));
  }
}