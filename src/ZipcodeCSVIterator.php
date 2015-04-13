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

	/**
	 * CSV columns
	 * @link http://www.post.japanpost.jp/zipcode/dl/readme.html
	 */
	const COL_JIS_CODE				= 0;	// 1.全国地方公共団体コード(JIS X0401、X0402)
	const COL_OLD_ZIPCODE			= 1;	// 2.(旧)郵便番号(5桁)
	const COL_ZIPCODE				= 2;	// 3.郵便番号(7桁)
	const COL_PREF_KANA				= 3;	// 4.都道府県名(半角カタカナ)
	const COL_CITY_KANA				= 4;	// 5.市区町村名(半角カタカナ)
	const COL_COMMUNITY_AREA_KANA	= 5;	// 6.町域名(半角カタカナ)
	const COL_PREF					= 6;	// 7.都道府県名
	const COL_CITY					= 7;	// 8.市区町村名
	const COL_COMMUNITY_AREA		= 8;	// 9.町域名
	const COL_SEPARATE_FLG			= 9;	// 10.一町域が二以上の郵便番号で表される場合の表示(1:該当/0:該当せず)
	const COL_FLG_11				= 10;	// 11.小字毎に番地が起番されている町域の表示(1:該当/0:該当せず)
	const COL_FLG_12				= 11;	// 12.丁目を有する町域の場合の表示(1:該当/0:該当せず)
	const COL_DUPLICATE_FLG			= 12;	// 13.一つの郵便番号で二以上の町域を表す場合の表示(1:該当/0:該当せず)
	const COL_UPDATE_KN				= 13;	// 14.更新区分(0:変更なし/1:変更あり)
	const COL_UPDATE_REASON_KN		= 14;	// 15.変更理由区分

	private $_prev_zipcode;

	/**
	 * 読み込み行の郵便番号・住所の取得
	 *
	 * @return \ArrayObject
	 */
	public function current() {
		$row = parent::current();
		return new \ArrayObject(array(
			'jis_code'				=> $row[self::COL_JIS_CODE],
			'old_zipcode'			=> rtrim($row[self::COL_OLD_ZIPCODE]),
			'zipcode'				=> $row[self::COL_ZIPCODE],
			'pref_kana'				=> $row[self::COL_PREF_KANA],
			'city_kana'				=> $row[self::COL_CITY_KANA],
			'community_area_kana'	=> $this->_convertCommunityArea($row[self::COL_COMMUNITY_AREA_KANA], true),
			'pref'					=> $row[self::COL_PREF],
			'city'					=> $row[self::COL_CITY],
			'community_area'		=> $this->_convertCommunityArea($row[self::COL_COMMUNITY_AREA]),
			'is_split'				=> $this->_isSplitAddress()
		), \ArrayObject::ARRAY_AS_PROPS);
	}

	/**
	 * @return void
	 */
	public function next() {
		$row = $this->getRawData();
		$this->_prev_zipcode = $row[self::COL_ZIPCODE];
		parent::next();
	}

	/**
	 * 生データ取得
	 *
	 * @return array
	 */
	public function getRawData() {
		return parent::current();
	}

	/**
	 * 住所が複数行に分割して記載されているデータかどうか
	 * (町域名の文字数が38文字を超える場合、複数レコードに分割して記載されている)
	 *
	 * @access	public
	 * @return	boolean
	 */
	private function _isSplitAddress() {
		$current_row = $this->getRawData();
		if ($current_row[self::COL_DUPLICATE_FLG] != '1'
			&& $current_row[self::COL_ZIPCODE] == $this->_prev_zipcode) {
			return true;
		}
		return false;
	}

	/**
	 * 町域名の加工
	 *
	 * @access	private
	 * @param	string	$community_area	町域名
	 * @return	string	加工後の町域名
	 */
	private function _convertCommunityArea($community_area, $is_kana = false) {
		static $markers = array(
			'（'		=> array('（',	'('),
			'一円'	=> array('一円',	'ｲﾁｴﾝ'),
			'場合'	=> array('場合',	'ｹｲｻｲｶﾞﾅｲﾊﾞｱｲ'),
			'）'		=> array('）',	')')
		);
		$marker_key = ($is_kana) ? 1 : 0;

		// 開始カッコ以降を除去
		if (($pos = mb_strpos($community_area, $markers['（'][$marker_key])) !== false) {
			$community_area = mb_substr($community_area, 0, $pos);
		}

		if ($this->_isIchienArea()) {
			$community_area = str_replace($markers['一円'][$marker_key], '', $community_area);
		}

		// 「以下に記載がない場合」、閉じカッコ対策
		if (mb_strpos($community_area, $markers['場合'][$marker_key]) !== false
			|| mb_strpos($community_area, $markers['）'][$marker_key]) !== false) {
			$community_area = '';
		}
		return $community_area;
	}

	/**
	 * 町域名が「一円」かを判定
	 *
	 * @return boolean
	 */
	private function _isIchienArea() {
		$row = $this->getRawData();
		// カナの場合、「一円」以外で住所の一部に「ｲﾁｴﾝ」を含む可能性があるため
		return (mb_strpos($row[self::COL_COMMUNITY_AREA], '一円') !== false);
	}
}
