<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../tcpdf/tcpdf_barcodes_1d.php';

$barcodeobj = new TCPDFBarcode($_GET['code'], 'C39');//'QRCODE,H');

$barcodeobj->getBarcodePNG(6, 6, array(0,0,0));
