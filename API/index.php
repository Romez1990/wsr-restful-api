<?php

$url = explode('/', key($_GET));

if (empty($url[0])) return;

$class_name = $url[0];
require $class_name . '.php';
$class = new $class_name($url, $_GET, $_POST);
echo $class->result;
