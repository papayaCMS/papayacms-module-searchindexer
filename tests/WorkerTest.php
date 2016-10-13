<?php
require_once(__DIR__.'/bootstrap.php');

class PapayaModuleSearchIndexerWorkerTest extends PapayaTestCase {
  /**
   * @covers PapayaModuleSearchIndexerWorker::pagesConnector
   */
  public function testPagesConnectorSet() {
    $worker = new PapayaModuleSearchIndexerWorker();
    $pagesConnector = $this
      ->getMockBuilder('PagesConnector')
      ->getMock();
    $this->assertSame($pagesConnector, $worker->pagesConnector($pagesConnector));
  }
}
