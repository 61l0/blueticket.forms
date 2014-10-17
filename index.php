<?php
error_reporting(E_ALL);
require_once ('object.class.php');

?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php

$bt = new blueticket_objects();

echo $bt->generateItems();

?>
