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
		if ($class->method === 'POST' &&
			count($url) === 1)
			$class->create();
		elseif ($class->method === 'POST' &&
			is_numeric($url[1]) &&
			count($url) === 2)
			$class->edit($url[1], $_POST['title'], $_POST['manufacturer'], $_POST['text'], $_POST['tags'], $_FILES['image']);
		elseif ($class->method === 'DELETE' &&
			is_numeric($url[1]) &&
			count($url) === 2)
			$class->delete($url[1]);
		elseif ($class->method === 'GET' &&
			count($url) === 1)
			$class->view($url[1]);
		elseif ($class->method === 'GET' &&
			is_numeric($url[1]) &&
			count($url) === 2)
			$class->view_one($url[1]);
		elseif ($class->method === 'POST' &&
			is_numeric($url[1]) &&
			$url[2] === 'comments' &&
			count($url) === 3)
			$class->comment($url[1], $_POST['author'], $_POST['text']);
		elseif ($class->method === 'DELETE' &&
			is_numeric($url[1]) &&
			$url[2] === 'comments' &&
			is_numeric($url[3]) &&
			count($url) === 4)
			$class->delete_comment($url[1], $url[3], $_POST['author'], $_POST['text']);
		elseif ($class->method === 'GET' &&
			$url[1] === 'tag' &&
			count($url) === 3)
			$class->search_by_tag($url[2]);
		break;
}
//*/
