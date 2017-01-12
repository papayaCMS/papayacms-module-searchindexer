<?php

class PapayaModuleElasticsearchConnectionException
    extends PapayaModuleElasticsearchException {

  /**
   * Severity information
   *
   * @var integer
   */
  const SEVERITY_INFO = 1;

  /**
   * Severity warning
   *
   * @var integer
   */
  const SEVERITY_WARNING = 2;

  /**
   * Severity error
   *
   * @var integer
   */
  const SEVERITY_ERROR = 3;

  /**
   * Severtiy of this exception
   *
   * @var integer
   */
  private $_severity = self::SEVERITY_ERROR;

  /**
   * Create expeiton an store values
   */
  public function __construct($url) {
    parent::__construct('Invalid search server connection to: '.$url, 0);
  }

  /**
   * Get exception severity
   */
  public function getSeverity() {
    return $this->_severity;
  }
}