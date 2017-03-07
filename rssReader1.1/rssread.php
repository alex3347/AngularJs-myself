<?php
header('Content-Type: application/json');
$feed = new DOMDocument();
$feed -> load('http://36kr.com/feed');
$json = array();

$json['title'] = $feed -> getElementsByTagName('channel') -> item(0) -> getElementsByTagName('title') -> item(0) -> firstChild -> nodeValue;
$json['description'] = $feed -> getElementsByTagName('channel') -> item(0) -> getElementsByTagName('description') -> item(0) -> firstChild -> nodeValue;
$json['link'] = $feed -> getElementsByTagName('channel') -> item(0) -> getElementsByTagName('link') -> item(0) -> firstChild -> nodeValue;

$items = $feed -> getElementsByTagName('channel') -> item(0) -> getElementsByTagName('item');

$json['item'] = array();

foreach ($items as $item) {

	$author = $item -> getElementsByTagName('author') -> item(0) -> firstChild -> nodeValue;
	$title = $item -> getElementsByTagName('title') -> item(0) -> firstChild -> nodeValue;
	$category = $item -> getElementsByTagName('category') -> item(0) -> firstChild -> nodeValue;
	$guid = $item -> getElementsByTagName('guid') -> item(0) -> firstChild -> nodeValue;
	$description = $item -> getElementsByTagName('description') -> item(0) -> firstChild -> nodeValue;
	$pubDate = $item -> getElementsByTagName('pubDate') -> item(0) -> firstChild -> nodeValue;

	$json['item'][] = array("author" => $author, "title" => $title, "category" => $category, "guid" => $guid, "pubDate" => $pubDate, "description" => $description);

}
$json = json_encode($json);
$callback = $_GET['callback'];
exit($callback."($json)");
?>