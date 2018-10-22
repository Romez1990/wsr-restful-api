<?php

$sql = "update shop_items_rows_values set value = ? where id_row = ? and id_item = ?";
$stmt = $pdo->prepare($sql)->execute(['5\\3', 94, 1]);