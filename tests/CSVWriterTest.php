<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */

use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass \ZipcodeCSV\CSVWriter
 */
class CSVWriterTest extends \PHPUnit_Framework_TestCase {

	static private $_filters	= array(
		'convert.iconv.utf-8/cp932'	=>	null,
		'convert.eol.lf/crlf'		=>	'EOL_LFToCRLFFilter'
	);

	public function setUp() {
		vfsStream::setup('dummy_dir');
	}

	public function testAppend() {
		$file_path = vfsStream::url('dummy_dir/dummy.csv');

		$writer = new \ZipcodeCSV\CSVWriter($file_path);
		foreach (self::$_filters as $filter_name => $filter_class) {
			$writer->addFilter($filter_name, $filter_class);
		}

		$dummy_data = $this->_getDummyRows();
		foreach ($dummy_data as $row) {
			$writer->append($row);
		}
		$writer->close();

		$this->assertEquals(
			file_get_contents(dirname(__FILE__) . '/dat/dummy_csv.csv'),
			file_get_contents($file_path)
		);
	}

	public function testClose() {
		$file_path = vfsStream::url('dummy_dir/dummy.csv');

		$writer = new \ZipcodeCSV\CSVWriter($file_path);
		$writer->close();
	}

	private function _getDummyRows() {
		return array(
			array('あいうえお', 'かきく"けこ', 'さしす,せそ', "たちつてと\r\nなにぬねの"),
			array('はひふへほ', 'まみむ\めも', 'や""ゆ""よ', 'ら,",り","るれろ', "わ\r\nお\r\nん")
		);
	}
}

/**
 * 変換フィルタ ベースクラス
 */
abstract class ConvertFilter extends \php_user_filter {
	public function filter($in, $out, &$consumed, $closing) {
		while ($bucket = stream_bucket_make_writeable($in)) {
			$bucket->data	= $this->_convert($bucket->data);
			$consumed		+= $bucket->datalen;
			stream_bucket_append($out, $bucket);
		}
		return PSFS_PASS_ON;
	}

	abstract protected function _convert($data);

}

/**
 * 改行コードフィルタ（LF->CRLF）
 */
class EOL_LFToCRLFFilter extends ConvertFilter {
	protected function _convert($data) {
		return rtrim($data, "\n") . "\r\n";
	}
}

/**
 * 改行コードフィルタ（CRLF->LF）
 */
class EOL_CRLFToLFFilter extends ConvertFilter {
	protected function _convert($data) {
		return rtrim($data, "\r\n") . "\n";
	}
}