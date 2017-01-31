<?php

class PapayaModuleElasticsearchConnectionContentJsonException extends
    PapayaModuleElasticsearchException {

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
  public function __construct() {
    parent::__construct('Unexpected search result format.', 0);
  }

  /**
   * Get exception severity
   */
  public function getSeverity() {
    return $this->_severity;
  }
}