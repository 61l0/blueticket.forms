<?php
/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: WriteHTML and RTL support
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('blueticket');
$pdf->SetTitle('blueticket - forms price list');
$pdf->SetSubject('blueticket - forms price list');
$pdf->SetKeywords('blueticket - forms price list');

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

// set header and footer fonts
//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(0, 0, 0);//PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(0);//PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(0);//PDF_MARGIN_FOOTER);
// set auto page breaks
$pdf->SetAutoPageBreak(FALSE, 0);//PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
//if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
//	require_once(dirname(__FILE__).'/lang/eng.php');
//	$pdf->setLanguageArray($l);
//}

// ---------------------------------------------------------

// set font
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

// background template
$img_file = 'http://localhost/blueticket.forms/tcpdf/templates/01/bg-main-landscape.jpg';
$pdf->Image($img_file, 0, 0, 319, 225, 'JPEG', '', '', false, 300, '', false, false, 0, false, false, true);

// product image background
$img_file = 'http://localhost/blueticket.forms/tcpdf/templates/01/deco01.jpg';
$pdf->Image($img_file, 5, 5, 287, 200, 'JPEG', '', '', false, 300, '', false, false, 0, false, false, true);

// logo
$img_file = 'http://localhost/blueticket.forms/tcpdf/templates/01/logo-white.png';
$pdf->Image($img_file, 20, 20, 50, 12, 'PNG', '', '', false, 300, '', false, false, 0, false, false, true);

// price circle
$img_file = 'http://localhost/blueticket.forms/tcpdf/templates/01/circle-pink.png';
$pdf->Image($img_file, 25, 95, 30, 30, 'PNG', '', '', false, 300, '', false, false, 0, false, false, true);

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

//$pdf->lastPage();
//Close and output PDF document
$pdf->Output('sample-pl.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
