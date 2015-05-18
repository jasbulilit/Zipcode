<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */

/**
 * @coversDefaultClass \ZipcodeCSV\CSVReader
 */
class CSVReaderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::__construct
	 */
	public function testSetCSVIterator() {
		$csv_filepath = dirname(__FILE__) . '/dat/dummy_csv.csv';
		$iterator_class = 'DummyCSVIterator';
		$reader = new \ZipcodeCSV\CSVReader($csv_filepath, null, $iterator_class);

		$this->assertTrue(is_a($reader->getIterator(), $iterator_class));
	}

	/**
	 * @covers ::__construct
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage $class_name must extends
	 */
	public function testSetInvalidIterator() {
		$csv_filepath = dirname(__FILE__) . '/dat/dummy_csv.csv';
		$reader = new \ZipcodeCSV\CSVReader($csv_filepath, null, 'ArrayIterator');
	}

	/**
	 * @covers ::getIterator
	 */
	public function testGetIterator() {
		$csv_filepath = dirname(__FILE__) . '/dat/dummy_csv.csv';
		$reader = new \ZipcodeCSV\CSVReader($csv_filepath);

		$this->assertTrue(is_a($reader->getIterator(), '\ZipcodeCSV\CSVIterator'));
	}
}

class DummyCSVIterator extends \ZipcodeCSV\CSVIterator {}
