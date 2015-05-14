<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */

/**
 * @coversDefaultClass \ZipcodeCSV\AbstractCSV
 */
class AbstractCSVTest extends \PHPUnit_Framework_TestCase {

	const ABSTRACT_CSV	= '\ZipcodeCSV\AbstractCSV';
	const CSV_PATH		= '/path/to/dummy.csv';

	/**
	 * @var \ZipcodeCSV\AbstractCSV
	 */
	private $_csv;

	protected function setUp() {
		$this->_csv = $this->getMockForAbstractClass(self::ABSTRACT_CSV, array(self::CSV_PATH));
	}

	/**
	 * @covers ::__construct
	 * @covers ::getCSVPath
	 */
	public function testConstructor() {
		$csv = $this->getMockForAbstractClass(self::ABSTRACT_CSV, array(self::CSV_PATH));
		$this->assertEquals(self::CSV_PATH, $csv->getCSVPath());
		$this->assertNull($csv->getContext());
	}

	/**
	 * @covers ::__construct
	 * @covers ::getContext
	 */
	public function testConstructorWithContext() {
		$context = stream_context_create();

		$csv = $this->getMockForAbstractClass(self::ABSTRACT_CSV, array(self::CSV_PATH, $context));
		$this->assertEquals($context, $csv->getContext());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructorWithInvalidContext() {
		$context = array(
			'Foo'	=> 'bar'
		);

		$csv = $this->getMockForAbstractClass(self::ABSTRACT_CSV, array(self::CSV_PATH, $context));
	}

	/**
	 * @covers ::getDelimiter
	 * @covers ::setDelimiter
	 */
	public function testDelimiter() {
		$this->assertEquals(',', $this->_csv->getDelimiter());

		$this->_csv->setDelimiter("\t");
		$this->assertEquals("\t", $this->_csv->getDelimiter());
	}

	/**
	 * @covers ::getEnclosure
	 * @covers ::setEnclosure
	 */
	public function testEnclosure() {
		$this->assertEquals('"', $this->_csv->getEnclosure());

		$this->_csv->setEnclosure("#");
		$this->assertEquals("#", $this->_csv->getEnclosure());
	}

	/**
	 * @covers ::getEscape
	 * @covers ::setEscape
	 */
	public function testEscape() {
		$this->assertEquals("\\", $this->_csv->getEscape());

		$this->_csv->setEscape("@");
		$this->assertEquals("@", $this->_csv->getEscape());
	}

	/**
	 * @covers ::getContext
	 * @covers ::setContext
	 */
	public function testContext() {
		$context = stream_context_create();

		$this->_csv->setContext($context);
		$this->assertEquals($context, $this->_csv->getContext());
	}

	/**
	 * @covers ::setContext
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetContextWithInvalidContext() {
		$context = array(
			'Foo'	=> 'bar'
		);

		$this->_csv->setContext($context);
	}

	/**
	 * @covers ::getFilters
	 * @covers ::addFilter
	 * @runInSeparateProcess
	 * @dataProvider filterProvider
	 */
	public function testAddFilter($filter_name, $filter_class) {
		$this->assertEquals(array(), $this->_csv->getFilters());

		$this->_csv->addFilter($filter_name, $filter_class);
		$this->assertEquals(array($filter_name => $filter_class), $this->_csv->getFilters());
	}

	/**
	 * @covers ::addFilter
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Filter already exists
	 * @runInSeparateProcess
	 * @dataProvider filterProvider
	 */
	public function testAddDuplicateFilter($filter_name, $filter_class) {
		$this->_csv->addFilter($filter_name, $filter_class);
		$this->_csv->addFilter($filter_name, $filter_class);
	}

	/**
	 * @covers ::addFilter
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Failed to register stream filter
	 */
	public function testAddFilterFailed() {
		$this->_csv->addFilter('dummy', 'DummyClass');
	}

	/**
	 * @covers ::hasFilter
	 * @runInSeparateProcess
	 * @dataProvider filterProvider
	 */
	public function testhasFilter($filter_name, $filter_class) {
		$this->assertFalse($this->_csv->hasFilter($filter_name));

		$this->_csv->addFilter($filter_name, $filter_class);
		$this->assertTrue($this->_csv->hasFilter($filter_name));
	}

	/**
	 * @covers ::removeFilter
	 * @runInSeparateProcess
	 * @dataProvider filterProvider
	 */
	public function testRemoveFilter($filter_name, $filter_class) {
		$this->_csv->addFilter($filter_name, $filter_class);
		$this->assertTrue($this->_csv->hasFilter($filter_name));

		$this->_csv->removeFilter($filter_name);
		$this->assertFalse($this->_csv->hasFilter($filter_name));
	}

	/**
	 * @covers ::removeFilter
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Filter not exists
	 */
	public function testRemoveNonexistFilter() {
		$filter_name = 'non-exist-filter';
		$this->assertFalse($this->_csv->hasFilter($filter_name));

		$this->_csv->removeFilter($filter_name);
	}

	/**
	 * @covers ::buildUri
	 */
	public function testBuildUriWithFilter() {
		$filter_keys = array();
		$filter_list = array(
			'convert.iconv.utf-8/cp932',
			'convert.eol.lf/crlf',
			'url_encode/%&?#|?+',
		);
		foreach ($filter_list as $filter_name) {
			$this->_csv->addFilter($filter_name, null);
			$filter_keys[] = urlencode($filter_name);
		}

		$method = new ReflectionMethod($this->_csv, 'buildUri');
		$method->setAccessible(true);

		$csv_path = 'dummy.csv';
		$chain = 'read';
		$uri = sprintf('php://filter/%s=%s/resource=%s', $chain, implode('|', $filter_keys), $csv_path);
		$this->assertEquals($uri, $method->invoke($this->_csv, $csv_path, $chain));
	}

	/**
	 * @covers ::buildUri
	 */
	public function testBuildUriWithoutFilter() {
		$method = new ReflectionMethod($this->_csv, 'buildUri');
		$method->setAccessible(true);

		$csv_path = 'dummy.csv';
		$this->assertEquals($csv_path, $method->invoke($this->_csv, $csv_path));
	}

	public function filterProvider() {
		return array(
			array('dummy_filter', 'DummyFilterClass'),
			array('build_in_filter', null)
		);
	}
}

class DummyClass {}
class DummyFilterClass extends php_user_filter {}
