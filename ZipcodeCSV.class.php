<?php
/**
 * 郵便番号CSV
 */
class ZipcodeCSV {

	// 郵便番号CSVの列情報
	// 【参考】http://www.post.japanpost.jp/zipcode/dl/readme.html
	public	$csv_columns = array(
		'jis_cd',				// 1.全国地方公共団体コード(JIS X0401、X0402)
		'old_zipcode',			// 2.(旧)郵便番号(5桁)
		'zipcode',				// 3.郵便番号(7桁)
		'pref_nm_kana',			// 4.都道府県名(半角カタカナ)
		'city_nm_kana',			// 5.市区町村名(半角カタカナ)
		'community_area_kana',	// 6.町域名(半角カタカナ)
		'pref_nm',				// 7.都道府県名
		'city',					// 8.市区町村名
		'community_area',		// 9.町域名
		'separate_flg',			// 10.一町域が二以上の郵便番号で表される場合の表示
		'11_flg',				// 11.小字毎に番地が起番されている町域の表示
		'12_flg',				// 12.丁目を有する町域の場合の表示
		'duplicate_flg',		// 13.一つの郵便番号で二以上の町域を表す場合の表示
		'update_kn',			// 14.更新区分(0:変更なし/1:変更あり)
		'update_reason_kn'		// 15.変更理由区分
	);

	private	$_tmpfile_prefix	= 'tmp';	// 一時ファイルのプレフィックス

	/**
	 * 郵便番号CSV加工処理
	 *
	 * @access	public
	 * @param	string	$csv_path	郵便番号CSVファイルパス
	 * @param	string	$save_path	保存ファイルパス(未指定時は$csv_pathに上書き)
	 * @return	boolean
	 */
	public function convert($csv_path, $save_path = null) {
		$tmp_filepath = tempnam(sys_get_temp_dir(), $this->_tmpfile_prefix);
		if (($fp_orig = fopen($csv_path, 'r')) === false
			|| ($fp_conv = fopen($tmp_filepath, 'w')) === false) {
			return false;
		}

		if ($save_path === null) {
			$save_path = $csv_path;
		}

		$processed		= null;
		$prev_zipcode	= null;
		while (! feof($fp_orig)) {
			$row = fgetcsv($fp_orig);
			if (empty($row)) {
				continue;
			}

			$row = array_combine($this->csv_columns, $row);

			// 複数行に分割記載しているデータ対策
			if ($this->_isSplitAddress($row, $prev_zipcode)) {
				continue;
			}

			// 町域名データ補正
			$row['community_area'] = $this->_convertCommunityAreaName(
				$row['community_area']
			);

			// 重複排除処理
			$unique_key	= $this->_getUniqueKey($row);
			if (! isset($processed[$unique_key])) {
				fputcsv($fp_conv, $this->_getSaveColumns($row));
			}

			$processed[$unique_key]	= true;
			$prev_zipcode			= $row['zipcode'];
		}
		fclose($fp_orig);
		fclose($fp_conv);

		return rename($tmp_filepath, $save_path);
	}

	/**
	 * 重複排除用の一意キー取得
	 * 
	 * @access	protected
	 * @param	array	$row	行データ
	 * @return	string
	 */
	protected function _getUniqueKey($row) {
		return sprintf(
			'%s_%s_%s_%s',
			$row['zipcode'], $row['pref_nm'], $row['city'], $row['community_area']
		);
	}

	/**
	 * 加工後に保存する列を取得
	 * 
	 * @access	protected
	 * @param	array	$row	行データ
	 * @return	string
	 */
	protected function _getSaveColumns($row) {
		return array(
			$row['jis_cd'],
			$row['zipcode'],
			$row['pref_nm'],
			$row['city'],
			$row['community_area']
		);
	}

	/**
	 * 住所が複数行に分割して記載されているデータかどうか
	 * (町域名の文字数が38文字を超える場合、複数レコードに分割して記載されている)
	 * 
	 * @access	private
	 * @param	array		$row			行データ
	 * @param	string	$prev_zip_cd	1つ前の郵便番号
	 * @return	boolean
	 */
	private function _isSplitAddress($row, $prev_zip_cd) {
		if ($row['duplicate_flg'] != '1' && $row['zipcode'] == $prev_zip_cd) {
			return true;
		}
		return false;
	}

	/**
	 * 町域名の加工
	 *
	 * @access	private
	 * @param	string	$community_area_nm	町域名
	 * @return	string	加工後の町域名
	 */
	private function _convertCommunityAreaName($community_area_nm) {
		if (($pos = mb_strpos($community_area_nm, '（')) !== false) {
			$community_area_nm = mb_substr($community_area_nm, 0, $pos);
		}

		$community_area_nm = str_replace('一円', '', $community_area_nm);

		if (mb_strpos($community_area_nm, '場合') !== false
			|| mb_strpos($community_area_nm, '）') !== false) {
			$community_area_nm = '';
		}
		return $community_area_nm;
	}
}
