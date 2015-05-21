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

	private static $_dummy_csv = array(
		'00001,"あいうえお","abcdefg",1',
		'01101,"かきくけこ","hijklmn",2',
		'01102,"さしすせそ","opqrstu",3'
	);

	/**
	 * @covers ::__construct
	 */
	public function testSetCSVIterator() {
		$csv_uri = getDataURI(self::$_dummy_csv);
		$iterator_class = 'DummyCSVIterator';
		$reader = new \ZipcodeCSV\CSVReader($csv_uri, null, $iterator_class);

		$this->assertTrue(is_a($reader->getIterator(), $iterator_class));
	}

	/**
	 * @covers ::__construct
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage $class_name must extends
	 */
	public function testSetInvalidIterator() {
		$csv_uri = getDataURI(self::$_dummy_csv);
		$reader = new \ZipcodeCSV\CSVReader($csv_uri, null, 'ArrayIterator');
	}

	/**
	 * @covers ::getIterator
	 */
	public function testGetIterator() {
		$csv_uri = getDataURI(self::$_dummy_csv);
		$reader = new \ZipcodeCSV\CSVReader($csv_uri);

		$this->assertTrue(is_a($reader->getIterator(), '\ZipcodeCSV\CSVIterator'));
	}

	/**
	 * IteratorAggregate
	 */
	public function testIteratorAggregate() {
		$csv_uri = getDataURI(self::$_dummy_csv);
		$reader = new \ZipcodeCSV\CSVReader($csv_uri);

		foreach ($reader as $k => $row) {
			$this->assertEquals(toCSV(self::$_dummy_csv[$k]), $row);
		}
	}
}

class DummyCSVIterator extends \ZipcodeCSV\CSVIterator {}
