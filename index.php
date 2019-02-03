<!DOCTYPE html>
<html lang="en">
<head>
	<title>API</title>
	<meta charset="UTF-8">
	<meta name="viewport"
	      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<style>
		body {
			background: #252526;
		}
		
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
</head>
<body>

<?php
$files = array_diff(scandir('html'), array('.', '..', 'style.css'));
foreach ($files as $file)
	echo "<a href=\"html/$file\">$file</a>";
?>

</body>
</html>