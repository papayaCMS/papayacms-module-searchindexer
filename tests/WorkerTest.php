<?php
require_once(__DIR__.'/bootstrap.php');
PapayaTestCase::defineConstantDefaults(
  [
    'PAPAYA_DB_TBL_TOPICS',
    'PAPAYA_DB_TBL_TOPICS_PUBLIC',
    'PAPAYA_DB_TBL_TOPICS_VERSIONS',
    'PAPAYA_DB_TBL_TOPICS_TRANS',
    'PAPAYA_DB_TBL_TOPICS_VERSIONS_TRANS',
    'PAPAYA_DB_TBL_TOPICS_PUBLIC_TRANS',
    'PAPAYA_DB_TBL_MODULES',
    'PAPAYA_DB_TBL_VIEWS',
    'PAPAYA_DB_TBL_BOXLINKS',
    'PAPAYA_DB_TBL_LNG'
  ]
);
class PapayaModuleSearchIndexerWorkerTest extends PapayaTestCase {
  /**
   * @covers PapayaModuleSearchIndexerWorker::pagesConnector
   */
  public function testPagesConnectorSet() {
    $worker = new PapayaModuleSearchIndexerWorker();
    $pagesConnector = $this
      ->getMockBuilder('PagesConnector')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertSame($pagesConnector, $worker->pagesConnector($pagesConnector));
  }

  /**
   * @covers PapayaModuleSearchIndexerWorker::pagesConnector
   */
  public function testPagesConnectorGet() {
    $worker = new PapayaModuleSearchIndexerWorker();
    $pagesConnector = $this
      ->getMockBuilder('PagesConnector')
      ->disableOriginalConstructor()
      ->getMock();
    $plugins = $this
      ->getMockBuilder('PapayaPluginLoader')
      ->getMock();
    $plugins
      ->expects($this->once())
      ->method('get')
      ->will($this->returnValue($pagesConnector));
    $papaya = $this->mockPapaya()->application(['plugins' => $plugins]);
    $worker->papaya($papaya);
    $this->assertSame($pagesConnector, $worker->pagesConnector());
  }

  /**
   * @covers PapayaModuleSearchIndexerWorker::page
   */
  public function testPageSet() {
    $worker = new PapayaModuleSearchIndexerWorker();
    $page = $this
      ->getMockBuilder('base_topic')
      ->getMock();
    $this->assertSame($page, $worker->page($page));
  }

  /**
   * @covers PapayaModuleSearchIndexerWorker::page
   */
  public function testPageGet() {
    $worker = new PapayaModuleSearchIndexerWorker();
    $this->assertInstanceOf('base_topic', $worker->page());
  }
}
