<?php

$url = explode('/', key($_GET));

if (!file_exists($url[0] . '.php')) return;
require_once $url[0] . '.php';
$class = new $url[0]();

switch ($url[0]) {
	case 'auth':
		if ($class->method === 'POST' && count($url) === 1) {
			$class->auth();
		}
		break;

	case 'product':
		if ($class->method === 'POST' && count($url) === 1) {
			$class->create();
		} elseif ($class->method === 'POST' && count($url) === 2 && is_numeric($url[1])) {
			$class->edit($url[1]);
		}
		break;
}
