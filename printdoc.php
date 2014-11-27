<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'object.class.php';

$doc = new printDocument();

$doc->print_document($_GET['document']);

echo 'ok';