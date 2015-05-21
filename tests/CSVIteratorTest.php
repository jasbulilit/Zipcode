<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */

/**
 * @coversDefaultClass \ZipcodeCSV\CSVIterator
 */
class CSVIteratorTest extends \PHPUnit_Framework_TestCase {

	private static $_dummy_csv = array(
		'00001,"あいうえお","abcdefg",1',
		'01101,"かきくけこ","hijklmn",2',
		'01102,"さしすせそ","opqrstu",3'
	);

	private static $_dummy_tsv = array(
		"90001	'あいうえお'	'abcdefg'	1",
		"91101	'かきくけこ'	'hijklmn'	2",
		"91102	'さしすせそ'	'opqrstu'	3"
	);

	/**
	 * @covers ::__construct
	 */
	public function testConstructor() {
		$delimiter = '	';
		$enclosure = "'";

		$iterator = new \ZipcodeCSV\CSVIterator(
			getDataURI(self::$_dummy_tsv),
			$delimiter,
			$enclosure
		);
		foreach (self::$_dummy_tsv as $row) {
			$this->assertEquals(toCSV($row, $delimiter, $enclosure), $iterator->current());
			$iterator->next();
		}
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructorWithContext() {
		$options['context'] = stream_context_create();

		$iterator = new \ZipcodeCSV\CSVIterator(getDataURI(self::$_dummy_csv), ',', '"', '\\', $options);
		foreach (self::$_dummy_csv as $row) {
			$this->assertEquals(toCSV($row), $iterator->current());
			$iterator->next();
		}
	}

	/**
	 * @covers ::current
	 */
	public function testCurrent() {
		$iterator = new \ZipcodeCSV\CSVIterator(getDataURI(self::$_dummy_csv));
		$this->assertEquals(toCSV(self::$_dummy_csv[0]), $iterator->current());
	}

	/**
	 * @covers ::next
	 */
	public function testNext() {
		$iterator = new \ZipcodeCSV\CSVIterator(getDataURI(self::$_dummy_csv));
		foreach (self::$_dummy_csv as $row) {
			$this->assertEquals(toCSV($row), $iterator->current());
			$iterator->next();
		}
	}

	/**
	 * @covers ::key
	 */
	public function testKey() {
		$iterator = new \ZipcodeCSV\CSVIterator(getDataURI(self::$_dummy_csv));
		foreach (self::$_dummy_csv as $key => $row) {
			$this->assertEquals($key, $iterator->key());
			$iterator->next();
		}
	}

	/**
	 * @covers ::valid
	 */
	public function testValid() {
		$iterator = new \ZipcodeCSV\CSVIterator(getDataURI(self::$_dummy_csv));
		foreach (self::$_dummy_csv as $row) {
			$this->assertTrue($iterator->valid());
			$iterator->next();
		}
//		$this->assertFalse($iterator->valid());
	}

	/**
	 * @covers ::rewind
	 */
	public function testRewind() {
		$iterator = new \ZipcodeCSV\CSVIterator(getDataURI(self::$_dummy_csv));

		$csv = null;
		foreach (self::$_dummy_csv as $row) {
			$csv = $iterator->current();
			$iterator->next();
		}
		$this->assertEquals(toCSV(self::$_dummy_csv[2]), $csv, 'brefore rewind');
		$iterator->rewind();
		$this->assertEquals(toCSV(self::$_dummy_csv[0]), $iterator->current(), 'after rewind');
	}
}
