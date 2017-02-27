<?php
require_once(__DIR__.'/bootstrap.php');
require_once(__DIR__.'/../src/Indexer/Connector.php');
require_once(__DIR__.'/../src/Indexer/Worker.php');

class PapayaModuleElasticsearchIndexerConnectorTest extends PapayaTestCase {
  /**
   * @covers PapayaModuleElasticsearchIndexerConnector::onPublishPage
   */
  public function testOnPublishPage() {
    $connector = new PapayaModuleElasticsearchIndexerConnector();
    $worker = $this
      ->getMockBuilder('PapayaModuleElasticsearchIndexerWorker')
      ->getMock();
    $worker
      ->expects($this->once())
      ->method('onPublishPage')
      ->will($this->returnValue(TRUE));
    $connector->worker($worker);
    $this->assertTrue($connector->onPublishPage([]));
  }

  /**
   * @covers PapayaModuleElasticsearchIndexerConnector::worker
   */
  public function testWorkerSet() {
    $connector = new PapayaModuleElasticsearchIndexerConnector();
    $worker = $this
      ->getMockBuilder('PapayaModuleElasticsearchIndexerWorker')
      ->getMock();
    $this->assertSame($worker, $connector->worker($worker));
  }

  /**
   * @covers PapayaModuleElasticsearchIndexerConnector::worker
   */
  public function testWorkerGet() {
    $connector = new PapayaModuleElasticsearchIndexerConnector();
    $this->assertInstanceOf('PapayaModuleElasticsearchIndexerWorker', $connector->worker());
  }

  /**
   * @covers PapayaModuleElasticsearchIndexerConnector::option
   */
  public function testOption() {
    $connector = new PapayaModuleElasticsearchIndexerConnector();
    $worker = $this
      ->getMockBuilder('PapayaModuleElasticsearchIndexerWorker')
      ->getMock();
    $worker
      ->expects($this->once())
      ->method('option')
      ->will($this->returnValue('searchindex'));
    $connector->worker($worker);
    $this->assertEquals('searchindex', $connector->option('ELASTICSEARCH_INDEX', 'index'));
  }
}
