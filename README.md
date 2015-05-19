# Zipcode
[![Build Status](https://travis-ci.org/jasbulilit/Zipcode.svg?branch=master)](https://travis-ci.org/jasbulilit/Zipcode)
[![Code Coverage](https://scrutinizer-ci.com/g/jasbulilit/Zipcode/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasbulilit/Zipcode/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasbulilit/Zipcode/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasbulilit/Zipcode/?branch=master)

郵便局のサイトからダウンロードしたCSVファイルを加工する
http://www.post.japanpost.jp/zipcode/download.html

ダウンロードしたままのSJISのファイルを渡すと、UTF-8に変換し、以下の加工処理した結果を保存する
* 町域名の「○○一円」、「以下に掲載がない場合」、カッコ書きで丁目指定等、住所以外の文字列を取り除いて正規化する
* 正規化後の住所で重複するものを取り除く

動作要件: PHP5.3.3以上

## Usage
``` php
$save_path = '/path/to/result.csv';
$zipcode = new \ZipcodeCSV\ZipcodeCSV('/path/to/KEN_ALL.CSV');
$zipcode->convert(new \ZipcodeCSV\CSVWriter($save_path));
```

