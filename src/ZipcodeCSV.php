<?php
/**
 * 郵便番号CSV
 *
 * @author	Jasmine
 * @link	https://github.com/jasbulilit/Zipcode
 * @package	ZipcodeCSV
 */
namespace ZipcodeCSV;

class ZipcodeCSV {

	private $_csv_path;
	private $_reader;

	/**
	 * @param string $csv_path	CSV filepath
	 */
	public function __construct($csv_path) {
		$this->_csv_path = $csv_path;

		$this->_reader = new CSVReader($csv_path, null, '\ZipcodeCSV\ZipcodeCSVIterator');
		$this->_reader->addFilter('convert.iconv.cp932/utf-8');
	}

	/**
	 * 郵便番号CSV加工処理
	 *
	 * @access	public
	 * @param	CSVWriter $writer
	 * @return	boolean
	 */
	public function convert($writer) {
		$orig_csv = $this->_reader->getIterator();

		$processed = null;
		foreach ($orig_csv as $row) {
			if (empty($row->jis_code)) {
				continue;
			}

			// 複数行に分割記載しているデータ対策
			// 分割された行に記載されているのはカッコ書きで説明書きが長いものなので、無視する
			if ($row->isSplitAddress()) {
				continue;
			}

			// 重複排除処理
			$unique_key = $this->_getUniqueKey($row->zipcode, $row->pref, $row->city, $row->community_area);
			if (! isset($processed[$unique_key])) {
				$writer->append($row->getArrayCopy());
			}

			$processed[$unique_key] = true;
		}
		unset($orig_csv);

		return true;
	}

	/**
	 * 重複排除用の一意キー取得
	 *
	 * @access	protected
	 * @param	string	$zipcode		行データ
	 * @param	string	$pref_nm		都道府県名
	 * @param	string	$city			町域名
	 * @param	string	$community_area	町域名
	 * @return	string
	 */
	protected function _getUniqueKey($zipcode, $pref_nm, $city, $community_area) {
		return sprintf(
			'%s_%s_%s_%s',
			$zipcode, $pref_nm, $city, $community_area
		);
	}
}
