<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */
use ZipcodeCSV\ZipcodeCSVRow;

/**
 * @coversDefaultClass \ZipcodeCSV\ZipcodeCSVIterator
 */
class ZipcodeCSVIteratorTest extends \PHPUnit_Framework_TestCase {

	private static $_dummy_csv = array(
		'00001,"060  ","0600000","ﾎｯｶｲﾄﾞｳ","ｻｯﾎﾟﾛｼﾁｭｳｵｳｸ","ﾏﾙﾏﾙﾁｮｳ","北海道","札幌市中央区","○○町",0,0,0,0,0,0',
		'01101,"060  ","0601234","ﾎｯｶｲﾄﾞｳ","ｻｯﾎﾟﾛｼﾁｭｳｵｳｸ","ﾄﾞｺﾄﾞｺﾏﾁ","北海道","札幌市中央区","どこそこ町",0,0,0,0,0,0'
	);

	/**
	 * @covers ::current
	 */
	public function testCurrent() {
		$iterator = new \ZipcodeCSV\ZipcodeCSVIterator(getDataURI(self::$_dummy_csv));

		$this->assertEquals(new ZipcodeCSVRow(toCSV(self::$_dummy_csv[0]), null), $iterator->current());
	}

	/**
	 * @covers ::next
	 */
	public function testNext() {
		$iterator = new \ZipcodeCSV\ZipcodeCSVIterator(getDataURI(self::$_dummy_csv));
		$iterator->next();

		$prev_row = toCSV(self::$_dummy_csv[0]);
		$prev_zipcode = $prev_row[2];
		$this->assertEquals(new ZipcodeCSVRow(toCSV(self::$_dummy_csv[1]), $prev_zipcode), $iterator->current());

		$class = new ReflectionClass($iterator);
		$prop = $class->getProperty('_prev_zipcode');
		$prop->setAccessible(true);
		$this->assertEquals($prev_zipcode, $prop->getValue($iterator));
	}
}
