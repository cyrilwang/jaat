<?php
include_once('simple_html_dom.php');
include_once('config.php');

$server_index = 0;
if ($_GET['server_index']) {
	$server_index = (int)$_GET['server_index'];
}
if ($server_index >=  sizeof($server)) {
	$server_index = sizeof($server)-1;
}
$server_name = $server[$server_index][0];
$server_status_url = $server[$server_index][1];

$html = @file_get_html($server_status_url);
$json = array();
if (!$html) {
	$json['aaData'] = array();
	$json['error'] = "查詢 $server_name ($server_status_url) 時發生錯誤";
	print_r(json_encode($json));
	exit;
}

$server_info = array();
for ($index = 0; $index < 2; $index++) {
	$server_info_dl = $html->find('dl', $index);
	foreach ($server_info_dl->find('dt') as $value) {
		$server_info[] = $value->plaintext;
	}
}
$json['serverInfo'] = $server_info;

$process_table = $html->find('table', 0);
$process = array();
if ($process_table) {
    foreach ($process_table->find('tr') as $row) {
        $p1 = array();
        foreach ($row->find('td') as $value) {
            $p1[] = $value->plaintext;
        }
        if (count($p1) > 0) {
            $process[] = $p1;
        }
    }
    $json['aaData'] = $process;
}

$cache_table = $html->find('table', 2);
if ($cache_table) {
    $row = $cache_table->find('tr', 1);
	$cell = $row->find('td', 0);
    $json['cacheInfo'] = preg_split('/\r\n/', trim($row->plaintext));
}

print_r(json_encode($json));
exit;
?>
