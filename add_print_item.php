<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

$i = count($_SESSION['print_items']);

$_SESSION['print_items'][$i]['reg'] = $_REQUEST['reg'];
$_SESSION['print_items'][$i]['name'] = $_REQUEST['name'];
