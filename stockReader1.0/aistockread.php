<?php
function object_array($array) {//对象数组转换为数组
	if (is_object($array)) {
		$array = (array)$array;
	}
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			$array[$key] = object_array($value);
		}
	}
	return $array;
}
function stripContentTag($v){ //函数用于去掉content中的html标签
$v = str_replace('<br />', '', $v); 
$v = preg_replace('%(<span\s*[^>]*>(.*)</span>)%Usi', '\2', $v); 
$v = preg_replace('%(<span>(.*)</span>)%Usi', '\2', $v); 
$v = preg_replace('%(<span\s*[^>]*>)%Usi', '', $v); 
$v = str_replace('</span>', '', $v); 
$v = preg_replace('%(<a\s*[^>]*>(.*)</a>)%Usi', '\2', $v); 
$v = preg_replace('%(<p\s*[^>]*>(.*)</p>)%Usi', '\2', $v); 
$v = preg_replace('%(<p[^>]*>)%Usi', '', $v); 
$v = str_replace('<p>', '', $v); 
$v = str_replace('</p>', '', $v); 
$v = str_replace('&nbsp;', '', $v); 
$v = str_replace('&amp;', '', $v); 
$v = preg_replace('%(<b\s*[^>]*>(.*)</b>)%Usi', '\2', $v); 
$v = preg_replace('%(<strong\s*[^>]*>(.*)</strong>)%Usi', '\2', $v); 
$v = preg_replace('%(\s*)%Usi', '', $v); //去掉所有空格
  return $v; 
} 
$time = $_GET['time'];
$now = $_GET['now'];
if ($time == $now) {
	$timeTemp = $time-1;
	$contents = file_get_contents('http://www.ourkp.com/newsapi/his?day='.$timeTemp);
	$decodeContents = json_decode($contents);//解码
	$arrayContents = object_array($decodeContents);//对象数组转换为数组
	$data = $arrayContents['data'];//获取数组中的data项
	$nid = $data[count($data)-1]['nid'];//获取最后一项nid的值
	$myfile = fopen($now.".doc", "w") or die("Unable to open file!");//删除之前有过的爬取
	fclose($myfile);
	sleep(2);
	for ($i = 0; $i < 20; $i++) {
		$nid = $nid+10;
		$contentsTemp = file_get_contents('http://www.ourkp.com/live/newslist/?type=1&lastid='.$nid);
		$arrayContentsTemp = json_decode($contentsTemp);//对象转换为对象数组
		$arrayContents = object_array($arrayContentsTemp);
		$dataTemp = $arrayContents["data"];
	
		$myfile = fopen($now.".doc", "a") or die("Unable to open file!");
		$count = count($dataTemp);
		for ($j = 0; $j < $count; $j++) {
		
			$content = "\xEF\xBB\xBF".stripContentTag($dataTemp[$j]['content']);
			$pub_time = "\xEF\xBB\xBF".$dataTemp[$j]['pub_time']."\r\n";
			fwrite($myfile, $content);
			fwrite($myfile, $pub_time);
		
		}
		fclose($myfile);
	}
	
} else {
	$contents = file_get_contents('http://www.ourkp.com/newsapi/his?day='.$time);
	$decodeContents = json_decode($contents);
	$arrayContents = object_array($decodeContents);
	$data = $arrayContents['data'];
	$myfile = fopen($time.".doc", "w") or die("Unable to open file!");
	$count = count($data);
	for ($i = 0; $i < $count; $i++) {
	
		$content = "\xEF\xBB\xBF".stripContentTag($data[$i]['content']);
		$pub_time = "\xEF\xBB\xBF".$data[$i]['pub_time']."\r\n";
		fwrite($myfile, $content);
		fwrite($myfile, $pub_time);
	
	}
	fclose($myfile);
	
}




$callback = $_GET['callback'];
exit($callback."('complete')");

?>