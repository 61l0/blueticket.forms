<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../tcpdf/tcpdf_barcodes_2d.php';

$barcodeobj = new TCPDF2DBarcode($_GET['code'], 'QRCODE,H');

$barcodeobj->getBarcodePNG(6, 6, array(0,0,0));
