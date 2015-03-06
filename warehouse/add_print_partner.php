<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

$id = $_REQUEST['id'];

$_SESSION['selected_partner'] = $id;

echo 'ok';
