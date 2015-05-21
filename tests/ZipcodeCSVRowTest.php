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
 * @coversDefaultClass \ZipcodeCSV\ZipcodeCSVRow
 */
class ZipcodeCSVRowTest extends \PHPUnit_Framework_TestCase {

	private static $_dummy_csv = array(
		'normal'		=> '00001,"060  ","0600000","ﾎｯｶｲﾄﾞｳ","ｻｯﾎﾟﾛｼﾁｭｳｵｳｸ","ﾏﾙﾏﾙﾁｮｳ","北海道","札幌市中央区","○○町",0,0,0,0,0,0',
		'ikanikeisai'	=> '01101,"060  ","0600000","ﾎｯｶｲﾄﾞｳ","ｻｯﾎﾟﾛｼﾁｭｳｵｳｸ","ｲｶﾆｹｲｻｲｶﾞﾅｲﾊﾞｱｲ","北海道","札幌市中央区","以下に掲載がない場合",0,0,0,0,0,0',
		'ichien'		=> '01101,"060  ","0601234","ﾎｯｶｲﾄﾞｳ","ｻｯﾎﾟﾛｼﾁｭｳｵｳｸ","ﾄﾞｺﾄﾞｺﾏﾁｲﾁｴﾝ","北海道","札幌市中央区","どこそこ町一円",0,0,0,0,0,0',
		'not_ichien'	=> '28224,"65604","6560461","ﾋｮｳｺﾞｹﾝ","ﾐﾅﾐｱﾜｼﾞｼ","ｲﾁｴﾝｷﾞｮｳｼﾞ","兵庫県","南あわじ市","市円行寺",0,0,0,0,0,0',
		'kakko'			=> '01101,"060  ","0600042","ﾎｯｶｲﾄﾞｳ","ｻｯﾎﾟﾛｼﾁｭｳｵｳｸ","ﾏﾙﾏﾙﾁｮｳ(1-19ﾁｮｳﾒ)","北海道","札幌市中央区","○○町（１～１９丁目）",1,0,1,0,0,0',
		'start_kakko'	=> '01101,"060  ","0600042","ﾎｯｶｲﾄﾞｳ","ｻｯﾎﾟﾛｼﾁｭｳｵｳｸ","ﾏﾙﾏﾙﾁｮｳ(1-19ﾁｮｳﾒ、25","北海道","札幌市中央区","○○町（１～１９丁目、２５",1,0,1,0,0,0',
		'end_kakko'		=> '01101,"060  ","0600042","ﾎｯｶｲﾄﾞｳ","ｻｯﾎﾟﾛｼﾁｭｳｵｳｸ","-29ﾁｮｳﾒ)","北海道","札幌市中央区","～２９丁目）",1,0,1,0,0,0',
		'multi'			=> '40382,"807  ","8070042","ﾌｸｵｶｹﾝ","ｵﾝｶﾞｸﾞﾝﾐｽﾞﾏｷﾏﾁ","ﾖｼﾀﾞﾀﾞﾝﾁ","福岡県","遠賀郡水巻町","吉田団地",0,0,0,1,0,0'
	);

	/**
	 * @covers ::__construct
	 * @covers ::getRawData
	 */
	public function testConstructor() {
		$csv_row = toCSV(self::$_dummy_csv['normal']);
		$prev_zipcode = '1234567';

		$obj = new ZipcodeCSVRow($csv_row, $prev_zipcode);
		$this->assertEquals($csv_row, $obj->getRawData());

		$class = new ReflectionClass($obj);
		$prop = $class->getProperty('_prev_zipcode');
		$prop->setAccessible(true);
		$this->assertEquals($prev_zipcode, $prop->getValue($obj));
	}

	/**
	 * 通常のデータ
	 */
	public function testNoumalData() {
		$csv_row = toCSV(self::$_dummy_csv['normal']);
		$obj = new ZipcodeCSVRow($csv_row, null);

		$this->assertEquals($csv_row[ZipcodeCSVRow::COL_JIS_CODE], $obj->jis_code);
		$this->assertEquals(trim($csv_row[ZipcodeCSVRow::COL_OLD_ZIPCODE]), $obj->old_zipcode);
		$this->assertEquals($csv_row[ZipcodeCSVRow::COL_ZIPCODE], $obj->zipcode);
		$this->assertEquals($csv_row[ZipcodeCSVRow::COL_PREF_KANA], $obj->pref_kana);
		$this->assertEquals($csv_row[ZipcodeCSVRow::COL_CITY_KANA], $obj->city_kana);
		$this->assertEquals($csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA_KANA], $obj->community_area_kana);
		$this->assertEquals($csv_row[ZipcodeCSVRow::COL_PREF], $obj->pref);
		$this->assertEquals($csv_row[ZipcodeCSVRow::COL_CITY], $obj->city);
		$this->assertEquals($csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA], $obj->community_area);

		$this->assertFalse($obj->isSplitAddress());
	}

	/**
	 * 「以下に掲載がない場合」の町域名テスト
	 */
	public function testAddressWithIkanikeisai() {
		$csv_row = toCSV(self::$_dummy_csv['ikanikeisai']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA];

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertNotEquals($orig_address, $obj->community_area);
		$this->assertEquals('', $obj->community_area);
	}

	/**
	 * 「以下に掲載がない場合」の町域名カナテスト
	 */
	public function testAddressKanaWithIkanikeisai() {
		$csv_row = toCSV(self::$_dummy_csv['ikanikeisai']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA_KANA];

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertNotEquals($orig_address, $obj->community_area_kana);
		$this->assertEquals('', $obj->community_area_kana);
	}

	/**
	 * 「～一円」の町域名テスト
	 */
	public function testAddressWithIchien() {
		$csv_row = toCSV(self::$_dummy_csv['ichien']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA];
		$converted = str_replace('一円', '', $orig_address);

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertNotEquals($orig_address, $obj->community_area);
		$this->assertEquals($converted, $obj->community_area);
	}

	/**
	 * 「～一円」の町域名カナテスト
	 */
	public function testAddressKanaWithIchien() {
		$csv_row = toCSV(self::$_dummy_csv['ichien']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA_KANA];
		$converted = str_replace('ｲﾁｴﾝ', '', $orig_address);

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertNotEquals($orig_address, $obj->community_area_kana);
		$this->assertEquals($converted, $obj->community_area_kana);
	}

	/**
	 * 町域名カナに「ｲﾁｴﾝ」を含むテスト
	 */
	public function testAddressKanaWithNotIchien() {
		$csv_row = toCSV(self::$_dummy_csv['not_ichien']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA_KANA];

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertEquals($orig_address, $obj->community_area_kana);
	}

	/**
	 * 「○○町(xxx)」の町域名テスト
	 */
	public function testAddressWithKakko() {
		$csv_row = toCSV(self::$_dummy_csv['kakko']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA];

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertNotEquals($orig_address, $obj->community_area);
		$this->assertEquals('○○町', $obj->community_area);
	}

	/**
	 * 「○○町(xxx)」の町域名カナテスト
	 */
	public function testAddressKanaWithKakko() {
		$csv_row = toCSV(self::$_dummy_csv['kakko']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA_KANA];

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertNotEquals($orig_address, $obj->community_area_kana);
		$this->assertEquals('ﾏﾙﾏﾙﾁｮｳ', $obj->community_area_kana);
	}

	/**
	 * 「○○町(xxx」の町域名テスト(開始括弧のみ)
	 */
	public function testAddressWithStartKakko() {
		$csv_row = toCSV(self::$_dummy_csv['start_kakko']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA];

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertNotEquals($orig_address, $obj->community_area);
		$this->assertEquals('○○町', $obj->community_area);
	}

	/**
	 * 「○○町(xxx」の町域名カナテスト(開始括弧のみ)
	 */
	public function testAddressKanaWithStartKakko() {
		$csv_row = toCSV(self::$_dummy_csv['start_kakko']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA_KANA];

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertNotEquals($orig_address, $obj->community_area_kana);
		$this->assertEquals('ﾏﾙﾏﾙﾁｮｳ', $obj->community_area_kana);
	}

	/**
	 * 「xxx)」の町域名テスト(終了括弧のみ)
	 */
	public function testAddressWithEndKakko() {
		$csv_row = toCSV(self::$_dummy_csv['end_kakko']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA];

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertNotEquals($orig_address, $obj->community_area);
		$this->assertEquals('', $obj->community_area);
	}

	/**
	 * 「xxx)」の町域名カナテスト(終了括弧のみ)
	 */
	public function testAddressKanaWithEndKakko() {
		$csv_row = toCSV(self::$_dummy_csv['end_kakko']);
		$orig_address = $csv_row[ZipcodeCSVRow::COL_COMMUNITY_AREA_KANA];

		$obj = new ZipcodeCSVRow($csv_row, null);
		$this->assertNotEquals($orig_address, $obj->community_area_kana);
		$this->assertEquals('', $obj->community_area_kana);
	}

	/**
	 * 複数行分割時のテスト
	 */
	public function testSplitAddress() {
		$csv_row = toCSV(self::$_dummy_csv['end_kakko']);

		$obj = new ZipcodeCSVRow($csv_row, $csv_row[ZipcodeCSVRow::COL_ZIPCODE]);
		$this->assertTrue($obj->isSplitAddress());
	}
}
