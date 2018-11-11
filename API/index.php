<?php

$url = explode('/', key($_GET));

if (!file_exists($url[0] . '.php')) return;
require_once $url[0] . '.php';
$class = new $url[0];

switch ($url[0]) {
	case 'auth':
		if ($class->method === 'POST' && count($url) === 1) {
			$class->auth($_POST['login'], $_POST['password']);
		}
		break;
	
	case 'product':
		if ($class->method === 'POST' && count($url) === 1) {
			$class->create();
		} elseif ($class->method === 'POST' && count($url) === 2 && is_numeric($url[1])) {
			$class->edit($url[1], $_POST['title'], $_POST['manufacturer'], $_POST['text'], $_POST['tags'], $_FILES['image']);
		} elseif ($class->method === 'DELETE' && count($url) === 2 && is_numeric($url[1])) {
			$class->delete($url[1]);
		} elseif ($class->method === 'GET' && count($url) === 1) {
			$class->view($url[1]);
		} elseif ($class->method === 'GET' && count($url) === 2 && is_numeric($url[1])) {
			$class->view_one($url[1]);
		} elseif ($class->method === 'POST' && count($url) === 3 && is_numeric($url[1]) && $url[2] === 'comments') {
			$class->comment($url[1], $_POST['author'], $_POST['text']);
		} elseif ($class->method === 'DELETE' && count($url) === 3 && is_numeric($url[1]) && $url[2] === 'comments' && is_numeric($url[3])) {
			$class->delete_comment($url[1], $url[3], $_POST['author'], $_POST['text']);
		} elseif ($class->method === 'GET' && count($url) === 3 && $url[1] === 'tag') {
			$class->search_by_tag($url[2]);
		}
		break;
}
//*/
