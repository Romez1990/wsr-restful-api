<!DOCTYPE html>
<html lang="en">
<head>
	<title>Response</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>

<div id="response">
<?php
$url = explode('/', key($_GET));
if (empty($url[0])) return;

$class_name = $url[0];
require $class_name . '.php';
$class = new $class_name($url);
?>
</div>

<script>
	console.log(JSON.parse(document.getElementById('response').innerText));
</script>

</body>
</html>