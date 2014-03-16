#!/usr/bin/php
<?php
/**
 * @example Example entry for configuration:
 * /etc/munin/plugin-conf.d/weather
 *
 * [weather*]
 * user root
 */
require_once dirname(__FILE__).'/lib/simple_html_dom.php';

/**
 * url configs
 *
 * 気象庁ホームページから表示するURLを設定する
 */
$url_list = array(
	'Osaka'     => 'http://www.jma.go.jp/jp/amedas_h/today-62078.html',
	'Hiroshima' => 'http://www.jma.go.jp/jp/amedas_h/today-67437.html',
	'Sendai'    => 'http://www.jma.go.jp/jp/amedas_h/today-34392.html',
);


if(count($argv) == 2 && $argv[1] == 'autoconf') {
  echo "yes\n";
  exit(0);
}

if (count($argv) === 2 && $argv[1] === 'config') {
	echo "graph_title Outside temperature\n";
	echo "graph_category web:services\n";
	echo "graph_info This graph shows temperatures fetched from www.jma.go.jp.\n";
	echo "graph_vlabel temp in C\n";
    echo "graph_scale no\n";

	foreach($url_list as $location => $url){
		echo "{$location}.label {$location}\n";
		echo "{$location}.info  temperature ({$location})\n";
	}
	exit;
}

foreach($url_list as $location => $url){
	$temperature = get_temperature($url);
	echo "{$location}.value {$temperature}\n";
}


/**
 * 最新の気温を取得
 * @return string $url 気象庁の気象情報 詳細画面URL
 */
function get_temperature($url) {
	$contents = file_get_contents($url);
    $html = str_get_html($contents);
	$temperature = 0;
    if($html != false && method_exists($html,'find')){
        foreach($html->find('table[id=tbl_list] tr') as $tr){
			$text = @$tr->find('td.middle',0)->plaintext;
			if(preg_match('/^[0-9]+(\.[0-9]+)?$/',$text)){
				$temperature = $text;
			}
        }
        $html->clear();
    }
    unset($html);

	return $temperature;
}
?>
