<?php
require_once(__DIR__.'/bootstrap.php');

class PapayaModuleSearchIndexerWorkerTest extends PapayaTestCase {
  /**
   * @covers PapayaModuleSearchIndexerWorker::addToIndex
   */
  public function testAddToIndex() {
    $worker = new PapayaModuleSearchIndexerWorker();
    $writer = $this
      ->getMockBuilder('PapayaModuleSearchIndexerWriter')
      ->getMock();
    $writer
      ->expects($this->once())
      ->method('addToIndex')
      ->will($this->returnValue(TRUE));
    $worker->writer($writer);
    $this->assertTrue($worker->addToIndex(1, 'de', 'http://foo.example.com/', 'Content', 'Title'));
  }

  /**
   * @covers PapayaModuleSearchIndexerWorker::detectRedirection
   */
  public function testDetectRedirection() {
    $worker = new PapayaModuleSearchIndexerWorker_TestProxy();
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
   * @covers PapayaModuleSearchIndexerWorker::searchStatus
   */
  public function testSearchStatus() {
    $worker = new PapayaModuleSearchIndexerWorker_TestProxy();
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
   * @covers PapayaModuleSearchIndexerWorker::searchLocation
   */
  public function testSearchLocation() {
    $worker = new PapayaModuleSearchIndexerWorker_TestProxy();
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
   * @covers PapayaModuleSearchIndexerWorker::searchCookie
   */
  public function testSearchCookie() {
    $worker = new PapayaModuleSearchIndexerWorker_TestProxy();
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
   * @covers PapayaModuleSearchIndexerWorker::writer
   */
  public function testWriterSet() {
    $worker = new PapayaModuleSearchIndexerWorker();
    $writer = $this
      ->getMockBuilder('PapayaModuleSearchIndexerWriter')
      ->getMock();
    $this->assertSame($writer, $worker->writer($writer));
  }

  /**
   * @covers PapayaModuleSearchIndexerWorker::writer
   */
  public function testWriterInitialize() {
    $worker = new PapayaModuleSearchIndexerWorker();
    $this->assertInstanceOf('PapayaModuleSearchIndexerWriter', $worker->writer());
  }

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

  /**
   * @covers PapayaModuleSearchIndexerWorker::pagesConnector
   */
  public function testPagesConnectorGet() {
    $worker = new PapayaModuleSearchIndexerWorker();
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

class PapayaModuleSearchIndexerWorker_TestProxy extends PapayaModuleSearchIndexerWorker {
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