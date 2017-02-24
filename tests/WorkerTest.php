<?php
require_once(__DIR__.'/bootstrap.php');
require_once(__DIR__.'/../src/Indexer/Worker.php');
require_once(__DIR__.'/../src/Indexer/Writer.php');
require_once(__DIR__.'/../src/Connection.php');

class PapayaModuleElasticsearchIndexerWorkerTest extends PapayaTestCase {
  /**
   * @covers PapayaModuleElasticsearchIndexerWorker::addToIndex
   */
  public function testAddToIndex() {
    $worker = new PapayaModuleElasticsearchIndexerWorker();
    $writer = $this
      ->getMockBuilder('PapayaModuleElasticsearchIndexerWriter')
      ->getMock();
    $writer
      ->expects($this->once())
      ->method('addToIndex')
      ->will($this->returnValue(TRUE));
    $worker->writer($writer);
    $this->assertTrue($worker->addToIndex(1, 'de', 'http://foo.example.com/', 'Content', 'Title'));
  }

  /**
   * @covers PapayaModuleElasticsearchIndexerWorker::detectRedirection
   */
  public function testDetectRedirection() {
    $worker = new PapayaModuleElasticsearchIndexerWorker_TestProxy();
    $this->assertEquals(
      'http://foo.example.com/',
      $worker->detectRedirection(
        [
          'HTTP/1.1 302 Found',
          'Location: http://foo.example.com/'
        ]
      )
    );
  }
  /**
   * @covers PapayaModuleElasticsearchIndexerWorker::searchStatus
   */
  public function testSearchStatus() {
    $worker = new PapayaModuleElasticsearchIndexerWorker_TestProxy();
    $this->assertEquals(
      302,
      $worker->searchStatus(
        [
          'HTTP/1.1 302 Found'
        ]
      )
    );
  }
  /**
   * @covers PapayaModuleElasticsearchIndexerWorker::searchLocation
   */
  public function testSearchLocation() {
    $worker = new PapayaModuleElasticsearchIndexerWorker_TestProxy();
    $this->assertEquals(
      'http://foo.example.com/',
      $worker->searchLocation(
        [
          'HTTP/1.1 302 Found',
          'Location: http://foo.example.com/'
        ]
      )
    );
  }
  /**
   * @covers PapayaModuleElasticsearchIndexerWorker::searchCookie
   */
  public function testSearchCookie() {
    $worker = new PapayaModuleElasticsearchIndexerWorker_TestProxy();
    $this->assertEquals(
      [
        'foo=bar',
        'baz=tux'
      ],
      $worker->searchCookie(
        [
          'HTTP/1.1 200 OK',
          'Content-type: text/html',
          'Set-Cookie: foo=bar',
          'Set-Cookie: baz=tux'
        ]
      )
    );
  }

  /**
   * @covers PapayaModuleElasticsearchIndexerWorker::writer
   */
  public function testWriterSet() {
    $worker = new PapayaModuleElasticsearchIndexerWorker();
    $writer = $this
      ->getMockBuilder('PapayaModuleElasticsearchIndexerWriter')
      ->getMock();
    $this->assertSame($writer, $worker->writer($writer));
  }

  /**
   * @covers PapayaModuleElasticsearchIndexerWorker::writer
   */
  public function testWriterInitialize() {
    $worker = new PapayaModuleElasticsearchIndexerWorker();
    $this->assertInstanceOf('PapayaModuleElasticsearchIndexerWriter', $worker->writer());
  }

  /**
   * @covers PapayaModuleElasticsearchIndexerWorker::pagesConnector
   */
  public function testPagesConnectorSet() {
    $worker = new PapayaModuleElasticsearchIndexerWorker();
    $pagesConnector = $this
      ->getMockBuilder('PagesConnector')
      ->getMock();
    $this->assertSame($pagesConnector, $worker->pagesConnector($pagesConnector));
  }

  /**
   * @covers PapayaModuleElasticsearchIndexerWorker::pagesConnector
   */
  public function testPagesConnectorGet() {
    $worker = new PapayaModuleElasticsearchIndexerWorker();
    $pagesConnector = $this
      ->getMockBuilder('PagesConnector')
      ->getMock();
    $pluginLoader = $this
      ->getMockBuilder('PapayaPluginLoader')
      ->getMock();
    $pluginLoader
      ->expects($this->once())
      ->method('get')
      ->will($this->returnValue($pagesConnector));
    $worker->papaya($this->mockPapaya()->application(['plugins' => $pluginLoader]));
    $this->assertSame($pagesConnector, $worker->pagesConnector());
  }
}

class PapayaModuleElasticsearchIndexerWorker_TestProxy extends PapayaModuleElasticsearchIndexerWorker {
  public function searchCookie($headers) {
    return parent::searchCookie($headers);
  }

  public function searchLocation($headers) {
    return parent::searchLocation($headers);
  }

  public function searchStatus($headers) {
    return parent::searchStatus($headers);
  }

  public function detectRedirection($headers) {
    return parent::detectRedirection($headers);
  }
}
