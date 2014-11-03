<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

if (isset($_REQUEST['cnt']) && $_REQUEST['cnt'] > 0)
    $cnt = $_REQUEST['cnt'];
else
    $cnt = 1;

for ($k = 0; $k < $cnt; $k++) {
    $i = count($_SESSION['print_items']);

    $_SESSION['print_items'][$i]['reg'] = $_REQUEST['reg'];
    $_SESSION['print_items'][$i]['name'] = $_REQUEST['name'];
}

$i = count($_SESSION['selected_items']);

$_SESSION['selected_items'][$i]['reg'] = $_REQUEST['reg'];
$_SESSION['selected_items'][$i]['name'] = $_REQUEST['name'];
$_SESSION['selected_items'][$i]['qty'] = $cnt;
$_SESSION['selected_items'][$i]['price'] = $_REQUEST['price'];
$_SESSION['selected_items'][$i]['purchase_price'] = $_REQUEST['purchase_price'];

echo 'ok';
