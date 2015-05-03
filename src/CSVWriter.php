<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */
namespace ZipcodeCSV;

class CSVWriter extends AbstractCSV {

	/**
	 * @var resource
	 */
	private $_fp;

	/**
	 * @param string $csv_path		filepath
	 * @param resource $context		stream context resource
	 */
	public function __construct($csv_path, $context = null) {
		parent::__construct($csv_path, $context);

		$uri = $this->buildUri($csv_path, parent::FILTER_CHAIN_WRITE);

		$this->_fp = (isset($context)) ? fopen($uri, 'w', false, $context) : fopen($uri, 'w');
		if ($this->_fp === false) {
			throw new \RuntimeException('Failed to open file: ' . $uri);
		}
	}

	public function append(array $row) {
		if (version_compare(PHP_VERSION, '5.5.4') >= 0) {
			fputcsv($this->_fp, $row, $this->delimiter, $this->enclosure, $this->escape);
		} else {
			fputcsv($this->_fp, $row, $this->delimiter, $this->enclosure);
		}
	}
}
