<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */
namespace ZipcodeCSV;

class ZipcodeCSVIterator extends CSVIterator {

	private $_prev_zipcode;

	/**
	 * 読み込み行の郵便番号・住所の取得
	 *
	 * @return ZipcodeCSVRow
	 */
	public function current() {
		$row = parent::current();
		return new ZipcodeCSVRow($row, $this->_prev_zipcode);
	}

	/**
	 * @return void
	 */
	public function next() {
		$row = $this->current()->getRawData();
		$this->_prev_zipcode = $row[ZipcodeCSVRow::COL_ZIPCODE];
		parent::next();
	}
}
