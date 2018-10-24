<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<style>
		a {
			display: block;
			font-size: 40px;
			margin: 5px;
			color: #ADADAD;
			text-decoration: none;
		}
		
		a:hover {
			text-decoration: underline;
		}
	</style>
	<title>Document</title>
</head>
<body>

<?php

$files = array_diff(scandir('html'), array('.', '..', 'css'));

foreach ($files as $file)
	echo "<a href=\"html/$file\">$file</a>";

?>

</body>
</html>