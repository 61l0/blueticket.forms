<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PFBC\Form;
use PFBC\Element;

require_once("pfbc/PFBC/Form.php");
require_once("forms/blueticket_forms.php");

require_once ('tcpdf/tcpdf.php');
require_once ('tcpdf/tcpdf_barcodes_2d.php');

require_once 'php_socket.class.php';

class printDocument {

    function getSeo($text) {
        $utf8table = array("\xc3\xa1" => "a", "\xc3\xa4" => "a", "\xc4\x8d" => "c", "\xc4\x8f" => "d", "\xc3\xa9" => "e", "\xc4\x9b" => "e", "\xc3\xad" => "i", "\xc4\xbe" => "l", "\xc4\xba" => "l", "\xc5\x88" => "n", "\xc3\xb3" => "o", "\xc3\xb6" => "o", "\xc5\x91" => "o", "\xc3\xb4" => "o", "\xc5\x99" => "r", "\xc5\x95" => "r", "\xc5\xa1" => "s", "\xc5\xa5" => "t", "\xc3\xba" => "u", "\xc5\xaf" => "u", "\xc3\xbc" => "u", "\xc5\xb1" => "u", "\xc3\xbd" => "y", "\xc5\xbe" => "z", "\xc3\x81" => "A", "\xc3\x84" => "A", "\xc4\x8c" => "C", "\xc4\x8e" => "D", "\xc3\x89" => "E", "\xc4\x9a" => "E", "\xc3\x8d" => "I", "\xc4\xbd" => "L", "\xc4\xb9" => "L", "\xc5\x87" => "N", "\xc3\x93" => "O", "\xc3\x96" => "O", "\xc5\x90" => "O", "\xc3\x94" => "O", "\xc5\x98" => "R", "\xc5\x94" => "R", "\xc5\xa0" => "S", "\xc5\xa4" => "T", "\xc3\x9a" => "U", "\xc5\xae" => "U", "\xc3\x9c" => "U", "\xc5\xb0" => "U", "\xc3\x9d" => "Y", "\xc5\xbd" => "Z");
        $text = strtr($text, $utf8table);
        return $text;
    }

    function prepareLine($parLeftStr, $parRightStr, $parLength = 40) {
        $parLeftStr = $this->getSeo($parLeftStr);
        $parRightStr = $this->getSeo($parRightStr);

        if (strlen($parLeftStr) >= ($parLength - strlen($parRightStr))) {
            $parLeftStr = substr($parLeftStr, 0, ($parLength - strlen($parRightStr)) - 2);
        }

        $Lng = $parLength - strlen($parRightStr) - strlen($parLeftStr);

        $tempSpace = "";

        for ($i = 0; $i < $Lng; $i++)
            $tempSpace .= " ";

        $myString = chr(27) . "!" . chr(0) . "\n" . $parLeftStr . $tempSpace . $parRightStr;

        return $myString;
    }

    function prepareLinePdf($parLeftStr, $parRightStr, $parLength = 40) {
        $parLeftStr = $this->getSeo($parLeftStr);
        $parRightStr = $this->getSeo($parRightStr);

        if (strlen($parLeftStr) >= ($parLength - strlen($parRightStr))) {
            $parLeftStr = substr($parLeftStr, 0, ($parLength - strlen($parRightStr)) - 2);
        }

        $Lng = $parLength - strlen($parRightStr) - strlen($parLeftStr);

        $tempSpace = "";

        for ($i = 0; $i < $Lng; $i++)
            $tempSpace .= " ";

        $myString = "\n" . $parLeftStr . $tempSpace . $parRightStr;

        return $myString;
    }    
    
    function prepareHighLine($parLeftStr, $parRightStr, $parLength = 48) {
        $parLeftStr = $this->getSeo($parLeftStr);
        $parRightStr = $this->getSeo($parRightStr);

        if (strlen($parLeftStr) >= ($parLength - strlen($parRightStr))) {
            $parLeftStr = substr($parLeftStr, 0, ($parLength - strlen($parRightStr)) - 2);
        }

        $Lng = $parLength - strlen($parRightStr) - strlen($parLeftStr);

        $tempSpace = "";

        for ($i = 0; $i < $Lng; $i++)
            $tempSpace .= " ";

        $myString = "\n" . chr(27) . "!" . chr(17) . $parLeftStr . $tempSpace . $parRightStr . chr(27) . "!" . chr(0);

        return $myString;
    }

    function prepareHeaderPdf($par_DocumentNumber = "") {

        $header = "\n"; //chr(27) . chr(116) . chr(18);
//for($i=0;$i<255;$i++)
//{
//    $header .= chr(27) . "!" . chr($i) . "$i - ABC\n";
//}
        $blueticket = blueticket_forms_db::get_instance();
        $blueticket->query("SELECT * FROM invoices WHERE InvoiceNumber='$par_DocumentNumber'");
        $mydocument = $blueticket->row();

        $typeid = $mydocument['TypeID'];
        $customer = $mydocument['CustomerDescription'];
        $deliveryid = $mydocument['DeliveryTypeID'];
        $paymentid = $mydocument['PaymentTypeID'];
        $date = date('d.m.Y', strtotime($mydocument['InvoiceDateTime']));

        $blueticket->query("SELECT * FROM document_types WHERE ID='$typeid'");
        $mytype = $blueticket->row();

        $docname = $mytype['Description'];

        $blueticket->query("SELECT * FROM delivery_types WHERE ID='$deliveryid'");
        $mydelivery = $blueticket->row();

        $delivery = $mydelivery['Description'];

        $blueticket->query("SELECT * FROM payment_types WHERE ID='$paymentid'");
        $mypayment = $blueticket->row();
        $payment = $mypayment['PaymentTypeName'];

        $header .= $this->prepareLinePdf("", "========================================");
        $header .= $this->prepareLinePdf("$docname cislo:", $par_DocumentNumber);
        $header .= $this->prepareLinePdf("", "========================================");
        $header .= $this->prepareLinePdf("Partner:", "");
        $header .= $this->getSeo("\n" . $customer);
        $header .= $this->prepareLinePdf("", "========================================");
        $header .= $this->prepareLinePdf("Datum vystavenia:", $date);
        $header .= $this->prepareLinePdf("Datum dodania:", $date);
        $header .= $this->prepareLinePdf("", "========================================");
        $header .= $this->prepareLinePdf("Forma uhrady:", $payment);
        $header .= $this->prepareLinePdf("Sposob dodania:", $delivery);
        $header .= $this->prepareLinePdf("", "========================================");
//$header .=  prepareLine(" ", " ");

        return $header;
    }

        function prepareHeader($par_DocumentNumber = "") {

        $header = "\n"; //chr(27) . chr(116) . chr(18);
//for($i=0;$i<255;$i++)
//{
//    $header .= chr(27) . "!" . chr($i) . "$i - ABC\n";
//}
        $blueticket = blueticket_forms_db::get_instance();
        $blueticket->query("SELECT * FROM invoices WHERE InvoiceNumber='$par_DocumentNumber'");
        $mydocument = $blueticket->row();

        $typeid = $mydocument['TypeID'];
        $customer = $mydocument['CustomerDescription'];
        $deliveryid = $mydocument['DeliveryTypeID'];
        $paymentid = $mydocument['PaymentTypeID'];
        $date = date('d.m.Y', strtotime($mydocument['InvoiceDateTime']));

        $blueticket->query("SELECT * FROM document_types WHERE ID='$typeid'");
        $mytype = $blueticket->row();

        $docname = $mytype['Description'];

        $blueticket->query("SELECT * FROM delivery_types WHERE ID='$deliveryid'");
        $mydelivery = $blueticket->row();

        $delivery = $mydelivery['Description'];

        $blueticket->query("SELECT * FROM payment_types WHERE ID='$paymentid'");
        $mypayment = $blueticket->row();
        $payment = $mypayment['PaymentTypeName'];

        $header .= $this->prepareLine("", "========================================");
        $header .= $this->prepareHighLine("$docname cislo:", $par_DocumentNumber);
        $header .= $this->prepareLine("", "========================================");
        $header .= $this->prepareLine("Dodavatel:", "");
        $header .= $this->prepareLine("JD SLOVAKIA s.r.o.", "");
        $header .= $this->prepareLine("K. Nováckeho 8/7", "");
        $header .= $this->prepareLine("971 01", "Prievidza");
        $header .= $this->prepareLine("ICO:", "36 820 989");
        $header .= $this->prepareLine("DIC/IC-DPH:", "SK2022449814");
        $header .= $this->prepareLine("IBAN:", "SK96 0200 0000 0030 9103 4751");
        $header .= $this->prepareLine("SWIFT:", "SUBASKBX");
        $header .= $this->prepareLine("", "========================================");
        $header .= $this->prepareLine("Partner:", "");
        $header .= $this->getSeo("\n" . $customer);
//        if ($par_Desk != '-') {
//            $result = getQuery("SELECT * FROM partners WHERE ID='$par_Desk'");
//            $row = mysql_fetch_assoc($result);
//            mysql_free_result($result);
//            $header .= prepareLine("Cislo zakaznika:", $par_Desk);
//            $header .= prepareLine($row['Name'], "");
//            $header .= prepareLine($row['Address'], "");
//            $header .= prepareLine($row['ZIP'], $row['City']);
//            $header .= prepareLine("ICO:", $row['PID']);
//            $header .= prepareLine("DIC:", $row['VAT']);
//            $header .= prepareLine("IC-DPH:", $row['VATID']);
//        } else
//            $header .= prepareLine("Cislo zakaznika:", $par_Desk);
        $header .= $this->prepareLine("", "========================================");
        $header .= $this->prepareLine("Datum vystavenia:", $date);
        $header .= $this->prepareLine("Datum dodania:", $date);
        $header .= $this->prepareLine("", "========================================");
        $header .= $this->prepareLine("Forma uhrady:", $payment);
        $header .= $this->prepareLine("Sposob dodania:", $delivery);
        $header .= $this->prepareLine("", "========================================");
//$header .=  prepareLine(" ", " ");

        return $header;
    }
    
    function cut() {
        return chr(29) . chr(86) . chr(1);
    }

    function prepareFooter($parTotal, $parTotalWOTax = 0) {
        $parTotalWOTax = number_format($parTotal / 1.2, 2);
//$footer =  prepareLine(" ", " ");
        $footer .= $this->prepareLine("", "========================================");
        $footer .= $this->prepareLine("Spolu bez DPH:", number_format($parTotalWOTax, 2) . " EUR");
        $footer .= $this->prepareLine("DPH 20%:", number_format($parTotal - $parTotalWOTax, 2) . " EUR");
        $footer .= $this->prepareLine("", "========================================");
//$footer .=  prepareLine(" ", " ");
        $footer .= $this->prepareHighLine("Celkom s DPH:", number_format($parTotal, 2) . " EUR");
//$footer .=  prepareLine(" ", " ");
        $footer .= $this->prepareLine("", "========================================");
        $footer .= $this->prepareLine(" ", " ");
        $footer .= $this->prepareLine(" ", " ");
        $footer .= $this->prepareLine(" ", " ");
        $footer .= $this->prepareLine(" ", " ");
        $footer .= $this->prepareLine(" ", " ");
        $footer .= $this->prepareLine(" ", " ");
        $footer .= "\n";
        $footer .= $this->cut();
        return $footer;
    }

    function getPrinter($par_DocumentNumber) {
        $blueticket = blueticket_forms_db::get_instance();
        $blueticket->query("SELECT * FROM invoices WHERE InvoiceNumber='$par_DocumentNumber'");
        $mydocument = $blueticket->row();

        $typeid = $mydocument['TypeID'];

        $blueticket->query("SELECT * FROM document_types WHERE ID='$typeid'");
        $mytype = $blueticket->row();

        return $mytype['PrinterAddress'];
    }

    function print_document($par_DocumentNumber) {
        $serialorder = new phpSerial();
        $serialorder->deviceSet($this->getPrinter($par_DocumentNumber));

//        $blueticket = new blueticket_forms_db();
        $blueticket = blueticket_forms_db::get_instance();
        $blueticket->query("SELECT * FROM invoices_items WHERE InvoiceNumber='$par_DocumentNumber'");
        $mydocumentitems = $blueticket->result();
        $total = 0;
        $total_wo_tax = 0;

        $message = $this->prepareHeader($par_DocumentNumber);

        foreach ($mydocumentitems as $row) {
            $message .= $this->prepareLine($row['Name'], "");
            $message .= $this->prepareLine("DPH %:" . number_format($row['TAXPercent'], 2), "Mnoz.:" . number_format($row['Quantity'], 2) . " ks x JC:" . number_format($row['Price'], 2));
            $subtotal = number_format($row['Price'] * $row['Quantity'], 2);
            $total = $subtotal + $total;
            $total_wo_tax = $total_wo_tax + ($subtotal / (1 + ($row['TaxPercent'] / 100)));
            $message .= $this->prepareLine("Spolu za polozku:", number_format($subtotal, 2));
            //getQuery("DELETE FROM print_kitchen WHERE ID=" . $row["ID"]);
        }

        $message .= $this->prepareFooter($total, $total_wo_tax);

        $serialorder->sendMessage($message);
    }

}

class InvoicePDF extends TCPDF {

    public function Header() {
        $bt = new blueticket_objects();

        $this->SetY(15);
        $this->SetFont('freesans', 'B', 8);
//$this->Cell(0, 10, '<em>' . $bt->getTranslatedText('LicenseOwner') . '</em>', 0, false, 'C', FALSE, '', 1);
        $this->MultiCell(175, 10, nl2br($bt->getTranslatedText('LicenseOwner')), 0, 'C', 0, 1, '', '', true, null, true);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('freesans', 'I', 8);
        $this->Cell(0, 10, 'Generované systémom blueticket™ (http://www.blueticket.eu)', 0, false, 'C');
    }

    public function CreateTextBox($textval, $x = 0, $y, $width = 0, $height = 10, $fontsize = 10, $fontstyle = '', $align = 'L') {
        $this->SetXY($x + 20, $y); // 20 = margin left
        $this->SetFont('freesans', $fontstyle, $fontsize);
        $this->Cell($width, $height, $textval, 0, false, $align);
    }

    function prepareHeader($par_DocumentNumber = "") {

        $print = new printDocument();

        $header = $print->prepareHeaderPdf($par_DocumentNumber);

        $this->SetY(35);
        $this->SetFont('freesans', 'B', 8);

        $this->MultiCell(85, 20, nl2br($header), 0, 'L', 0, 1, '120', '', true, null, true);
    }

    public function CreateInvoice($par_DocumentNumber) {
        //if (isset($_SESSION['selected_partner']) && $_SESSION['selected_partner'] > 0) {
        $bt = new blueticket_objects();

// create a PDF object
        $pdf = new InvoicePDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document (meta) information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('blueticket s.r.o.');
        $pdf->SetTitle('blueticket document');
        $pdf->SetSubject('Document');
        $pdf->SetKeywords('PDF');

// add a page
        $pdf->AddPage();
//
        $blueticket = blueticket_forms_db::get_instance();
//
        $blueticket->query("SELECT * FROM invoices_items WHERE InvoiceNumber='$par_DocumentNumber'");
        $myitems = $blueticket->result();
//
//        $myrow = $blueticket->row();
//
//        $partner_id = $myrow['ID'];
//
//// create address box
//        $pdf->CreateTextBox($myrow['Name'], 0, 55, 80, 10, 10, 'B');
//        $pdf->CreateTextBox($myrow['Address'], 0, 60, 80, 10, 10);
//        $pdf->CreateTextBox($myrow['ZIP'] . ' ' . $myrow['City'], 0, 65, 80, 10, 10);
//        $pdf->CreateTextBox($myrow['State'], 0, 70, 80, 10, 10);
//
//// invoice title / number
//        if ($par_purchase == TRUE)
//            $pdf->CreateTextBox($bt->getTranslatedText('PurchaseCard') . ' #201012345', 0, 90, 120, 20, 16);
//        else
//            $pdf->CreateTextBox($bt->getTranslatedText('IssueCard') . ' #201012345', 0, 90, 120, 20, 16);
// date, order ref
        $pdf->prepareHeader($par_DocumentNumber);
        //$pdf->CreateTextBox($bt->getTranslatedText('Date') . ': ' . date('d-m-Y'), 0, 100, 0, 10, 10, '', 'R');
//$pdf->CreateTextBox('Order ref.: #6765765', 0, 105, 0, 10, 10, '', 'R');
//items
// list headers
        $pdf->CreateTextBox($bt->getTranslatedText('Quantity'), 0, 120, 20, 10, 10, 'B', 'C');
        $pdf->CreateTextBox($bt->getTranslatedText('Name'), 20, 120, 90, 10, 10, 'B');
        $pdf->CreateTextBox($bt->getTranslatedText('Price'), 110, 120, 30, 10, 10, 'B', 'R');
        $pdf->CreateTextBox($bt->getTranslatedText('Subtotal'), 140, 120, 30, 10, 10, 'B', 'R');

        $pdf->Line(20, 129, 195, 129);

        $currY = 128;
        $total = 0;
        foreach ($myitems as $row) {
            $btdb = blueticket_forms_db::get_instance();

            $price = $row['Price'];

            $btdb->query("SELECT * FROM items WHERE RegistrationNumber='" . $row['Barcode'] . "'");
            $myrow = $btdb->row();
            $tax = $myrow['TaxID'];
            $type = $myrow['TypeID'];
            $btdb->query("SELECT * FROM units WHERE ID='" . $myrow['UnitID'] . "'");
            $myrow = $btdb->row();

            $unit = $myrow['Name'];

            $btdb->query("SELECT * FROM taxes WHERE ID='$tax'");
            $myrow = $btdb->row();

            $tax = $myrow['Value'];

            //$btdb->query("INSERT INTO current_accounting(CartNr,RegistrationNumber,Name,Price,Unit,Tax,Qty,TypeID,Printed) VALUES('$partner_id','" . $row['reg'] . "', '" . $row['name'] . "',$price,'$unit',$tax, " . $row['qty'] . ", $type, 0)");
            $pdf->CreateTextBox($row['Quantity'], 0, $currY, 20, 10, 10, '', 'C');
            $pdf->CreateTextBox($row['Barcode'] . ' - ' . $row['Name'], 20, $currY, 90, 10, 10, '');
            $pdf->CreateTextBox(number_format($row['Price'], 2, '.', ' ') . ' €', 110, $currY, 30, 10, 10, '', 'R');
            $amount = $row['Quantity'] * $row['Price'];
            $pdf->CreateTextBox(number_format($amount, 2, '.', ' ') . ' €', 140, $currY, 30, 10, 10, '', 'R');
            $currY = $currY + 5;
            $total = $total + $amount;
        }
        $pdf->Line(20, $currY + 4, 195, $currY + 4);

//footer
//
// output the total row
        $pdf->CreateTextBox($bt->getTranslatedText('Total'), 5, $currY + 5, 135, 10, 10, 'B', 'R');
        $pdf->CreateTextBox(number_format($total, 2, '.', ' ') . ' €', 140, $currY + 5, 30, 10, 10, 'B', 'R');

// some payment instructions or information
        $pdf->setXY(20, $currY + 30);
        $pdf->SetFont('freesans', '', 10);
        $pdf->MultiCell(175, 10, $bt->getTranslatedText('InvoiceFooter'), 0, 'L', 0, 1, '', '', true, null, true);

//Close and output PDF document
        $pdf->Output('doc_' . $par_DocumentNumber . '.pdf', 'D');

//        } else {
//            header('location: index.php?report=partners');
//        }
    }

}

class PDFPrinter extends TCPDF {

    public function CreateTextBox($textval, $x = 0, $y, $width = 0, $height = 10, $fontsize = 4, $fontstyle = '', $align = 'L') {
        $this->SetXY($x, $y);
        $this->SetFont('freesans', $fontstyle, $fontsize);
        $this->Cell($width, $height, $textval, 0, false, $align);
    }

    public function CreateBarcode($code, $x, $y, $w, $h) {
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'courier',
            'fontsize' => 8,
            'stretchtext' => 4
        );

        $this->write1DBarcode($code, 'C39', $x, $y, $w, $h, 0.4, $style, 'N');
    }

    public function CreateQRCode($code, $x, $y, $w, $h) {
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'courier',
            'fontsize' => 8,
            'stretchtext' => 4
        );

        $this->write2DBarcode($code, 'QRCODE', $x, $y, $w, $h, $style, 'N');
    }

}

class blueticket_objects {

    public $lang = 'sk';

    function __construct() {
//$this->lang = 'sk';
    }

    public function generateMainScreen() {
        $form = new Form("form-main");
        $button = new Element\Button("Číselníky");
        $form->addElement($button);
        $form->render();
    }

    public function generateDesks() {
        $blueticket = blueticket_forms::get_instance();

        $blueticket->table('desks_pages'); //nazov tabulky v databaze
        $blueticket->order_by('PageName', 'ASC');
        $blueticket->default_tab($this->getTranslatedText('DesksPages'));
        $blueticket->table_name($this->getTranslatedText('DesksPages')); //titulok zobrazenia tabulky na stranke

        $blueticket->columns('PageName,Description'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->fields('PageName,Description'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni

        $blueticket->label('PageName', $this->getTranslatedText('PageName'));
        $blueticket->label('Description', $this->getTranslatedText('Description'));

        $bt_desks = $blueticket->nested_table($this->getTranslatedText('Desks'), 'ID', 'desks', 'Page');
        $bt_desks->columns('Name,FromRow,FromColumn,Rowspan,Colspan');
        $bt_desks->fields('Name,FromRow,FromColumn,Rowspan,Colspan');

        // items
        $bt_item_invoice = $bt_desks->nested_table($this->getTranslatedText('InvoicesItems'), 'Name', 'invoices_items', 'CartNr');
        $bt_item_invoice->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, CartNr, Group, Quantity, Price, SubTotal');
        //$bt_item_invoice->subselect('InvoiceDateTime', 'SELECT MAX(InvoiceDateTime) as InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');
        $bt_item_invoice->order_by('InvoiceDateTime', 'DESC');
        $bt_item_invoice->subselect('SubTotal', '{Price}*{Quantity}');
        $bt_item_invoice->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice->change_type('Quantity,Price,SubTotal', 'price', '0');
        $bt_item_invoice->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice->relation("Group", "groups", "ID", "Name");

        $bt_item_invoice->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $bt_item_invoice->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice->label('Group', $this->getTranslatedText('GroupID'));

        $bt_item_invoice->sum('SubTotal');


// invoices items month nested table
        $bt_item_invoice_month = $bt_desks->nested_table($this->getTranslatedText('InvoicesItemsMonth'), 'Name', 'invoices_items_month', 'CartNr');
        $bt_item_invoice_month->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, CartNr, Quantity, Price, SubTotal');
        //$bt_item_invoice_month->subselect('InvoiceDateTime', 'SELECT MAX(InvoiceDateTime) as InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');
        $bt_item_invoice_month->order_by('InvoiceDateTime', 'DESC');
        $bt_item_invoice_month->subselect('SubTotal', '{Price}*{Quantity}');

        $bt_item_invoice_month->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $bt_item_invoice_month->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice_month->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice_month->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice_month->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice_month->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice_month->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice_month->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice_month->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice_month->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice_month->change_type('Quantity,Price,SubTotal', 'price', '0');
        $bt_item_invoice_month->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice_month->relation("Group", "groups", "ID", "Name");

        $bt_item_invoice_month->sum('SubTotal');
        //items

        return $blueticket->render();
    }

    public function generateGroups() {
        $blueticket = blueticket_forms::get_instance();

        $blueticket->table('groups'); //nazov tabulky v databaze
        $blueticket->order_by('Name', 'ASC');
        $blueticket->table_name($this->getTranslatedText('Groups')); //titulok zobrazenia tabulky na stranke
        $blueticket->default_tab($this->getTranslatedText("Groups"));
        $blueticket->columns('Name,POSEnabled'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->fields('Name,POSEnabled'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni

        $blueticket->label('Name', $this->getTranslatedText('Name'));
        $blueticket->label('POSEnabled', $this->getTranslatedText('POSEnabled'));

        $blueticket->relation('POSEnabled', 'responses', 'ID', 'Response');

        $bt_items = $blueticket->nested_table($this->getTranslatedText("Items"), 'ID', 'items', 'GroupID');
        $bt_items->table_name($this->getTranslatedText('Items')); //titulok zobrazenia tabulky na stranke

        $bt_items->columns('PLU,RegistrationNumber,Name,Qty,UnitID,Price,PurchasePrice,GroupID,SubtotalPrice,SubtotalPurchasePrice'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $bt_items->fields('Barcode,PLU,Name,Qty,UnitID,Price,MinimalPrice,PurchasePrice,TaxID,GroupID,TypeID'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni

        $bt_items->label('PLU', $this->getTranslatedText('PLU'));
        $bt_items->label('RegistrationNumber', $this->getTranslatedText('RegistrationNumber'));
        $bt_items->label('Name', $this->getTranslatedText('Name'));
        $bt_items->label('Qty', $this->getTranslatedText('Qty'));
        $bt_items->label('UnitID', $this->getTranslatedText('UnitID'));
        $bt_items->label('Price', $this->getTranslatedText('Price'));
        $bt_items->label('MinimalPrice', $this->getTranslatedText('MinimalPrice'));
        $bt_items->label('PurchasePrice', $this->getTranslatedText('PurchasePrice'));
        $bt_items->label('TaxID', $this->getTranslatedText('TaxID'));
        $bt_items->label('GroupID', $this->getTranslatedText('GroupID'));
        $bt_items->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_items->label('TypeID', $this->getTranslatedText('TypeID'));

        $bt_items->column_pattern('Barcode', '<img style="width:90px; height:90px" src="inc/qrcode.php?code={RegistrationNumber}"/>');

        $bt_items->relation('UnitID', 'units', 'ID', 'Name');
        $bt_items->relation('TaxID', 'taxes', 'ID', 'Value');
        $bt_items->relation('GroupID', 'groups', 'ID', 'Name');
        $bt_items->relation('TypeID', 'item_types', 'ID', 'Name');

        $bt_items->subselect('SubtotalPrice', '{Price}*{Qty}');
        $bt_items->subselect('SubtotalPurchasePrice', '{PurchasePrice}*{Qty}');

        $bt_items->highlight_row('PurchasePrice', '<', '{Price}', 'GreenYellow');
        $bt_items->highlight_row('PurchasePrice', '=', '{Price}', 'Yellow');
        $bt_items->highlight_row('PurchasePrice', '>', '{Price}', 'Orange');

        $bt_items->sum('SubtotalPrice, SubtotalPurchasePrice'); //  Zosumarizuje zvolene stlpce - berie do uvahy vsetky riadky filtrovanej tabulky

        $bt_items->label('SubtotalPrice', $this->getTranslatedText('SubtotalPrice'));
        $bt_items->label('SubtotalPurchasePrice', $this->getTranslatedText('SubtotalPurchasePrice'));

        $bt_items->column_class('Qty,Price,MinimalPrice,PurchasePrice,SubtotalPrice,SubtotalPurchasePrice,SellToday,SellHistory', 'align-right');
        $bt_items->change_type('Price,MinimalPrice,PurchasePrice,SubtotalPrice,SubtotalPurchasePrice,SellToday,SellHistory', 'price', '0');
        $bt_items->before_insert('before_items_insert_callback', 'blueticket.pos.functions.php');

        $bt_item_invoice = $blueticket->nested_table($this->getTranslatedText('InvoicesItems'), 'ID', 'invoices_items', 'Group');
        $bt_item_invoice->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice->subselect('SubTotal', '{Price}*{Quantity}');
        $bt_item_invoice->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice->relation("Group", "groups", "ID", "Name");

        $bt_item_invoice->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $bt_item_invoice->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice->change_type('Quantity,Price,SubTotal', 'price', '0');
        //$bt_item_invoice->subselect('InvoiceDateTime', 'SELECT InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');

        $bt_item_invoice->sum('SubTotal');

        $bt_item_invoice_month = $blueticket->nested_table($this->getTranslatedText('InvoicesItemsMonth'), 'ID', 'invoices_items_month', 'Group');
        $bt_item_invoice_month->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice_month->subselect('SubTotal', '{Price}*{Quantity}');
        $bt_item_invoice_month->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice_month->relation("Group", "groups", "ID", "Name");

        $bt_item_invoice_month->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $bt_item_invoice_month->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice_month->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice_month->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice_month->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice_month->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice_month->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice_month->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice_month->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice_month->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice_month->change_type('Quantity,Price,SubTotal', 'price', '0');
        //$bt_item_invoice_month->subselect('InvoiceDateTime', 'SELECT InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');

        $bt_item_invoice_month->sum('SubTotal');

        return $blueticket->render();
    }

    public function getTranslatedText($par_Text, $par_lang = 'sk') {
        $par_lang = $this->lang;

//$blueticket_db = new blueticket_forms_db();
        $blueticket_db = blueticket_forms_db::get_instance();

        $blueticket_db->query("CREATE TABLE IF NOT EXISTS translate (ID BIGINT NOT NULL AUTO_INCREMENT,TextToTranslate TEXT NULL,TranslatedText TEXT NULL,Lang TEXT NULL,PRIMARY KEY (ID)) ENGINE=MyISAM;");

        $blueticket_db = blueticket_forms_db::get_instance();
        $blueticket_db->query("SELECT * FROM translate WHERE TextToTranslate='$par_Text' AND Lang='$par_lang'");
        $myrow = $blueticket_db->row();

        if (strlen($myrow['TranslatedText']) > 0) {
            $par_Text = $myrow['TranslatedText'];
        } else {
            $blueticket_db->query("INSERT INTO translate(TextToTranslate,TranslatedText,Lang) VALUES('$par_Text','$par_Text','$par_lang')");
            $par_Text = $par_Text;
        }
        return $par_Text;
    }

    function generateMovements() {
        $bt_item_invoice_month = blueticket_forms::get_instance();

        $bt_item_invoice_month->table('invoices_items_month');
        $bt_item_invoice_month->table_name('InvoicesItemsMonth');
        $bt_item_invoice_month->order_by('InvoiceDateTime', 'DESC');

        $bt_item_invoice_month->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice_month->subselect('SubTotal', '{Price}*{Quantity}');
        $bt_item_invoice_month->relation("Group", "groups", "ID", "Name");

        $bt_item_invoice_month->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $bt_item_invoice_month->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice_month->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice_month->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice_month->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice_month->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice_month->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice_month->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice_month->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice_month->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice_month->change_type('Quantity,Price,SubTotal', 'price', '0');
        //$bt_item_invoice_month->subselect('InvoiceDateTime', 'SELECT MAX(InvoiceDateTime) FROM invoices WHERE InvoiceNumber={InvoiceNumber}');
        $bt_item_invoice_month->change_type('InvoiceDateTime', 'datetime');

        $bt_item_invoice_month->sum('SubTotal');

        return $bt_item_invoice_month->render();
    }

    function generateMenu() {
        $return = '<div style="width:100%; height:50px;padding-left:5px">';

        $return .= '<a href="?report=cards" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Karty</a>';
        $return .= '<a href="?report=stats" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Bloky</a>';
        $return .= '<a href="?report=docs" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Doklady</a>';
        $return .= '<a href="?report=movements" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Pohyby</a>';
        $return .= '<a href="?report=partners" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Partneri</a>';
        $return .= '<a href="?report=doctypes" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Typy dokladov</a>';
        $return .= '<a href="?report=desks" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Rozlozenia</a>';
        $return .= '<a href="?report=groups" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Skupiny</a>';
        $return .= '<a href="?report=units" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Jednotky</a>';
        $return .= '<a href="?report=trans" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Preklad</a>';
        $return .= '<a href="?report=users" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Uzivatelia</a>';
        $return .= '<a href="?report=logout" class="btn btn-primary" style="width:100px; height:30px; margin-top:5px; margin-right:5px">Odhlásiť</a>';

        $return .= '</div>';

        $return .= '<div style="clear:both"></div>';

        return $return;
    }

    function printItems($par_Type = 'UPC') {
        error_reporting(E_ALL);
//        session_start();

        $pdf = new PDFPrinter(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0, TRUE);
        $pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->AddPage();

        $x = 0;
        $y = 0;


        foreach ($_SESSION['print_items'] as $row) {
            if ($x == 5) {
                if ($y == 12) {
                    $x = 0;
                    $y = 0;
                    $pdf->AddPage();
                } else {
                    $y++;
                    $x = 0;
                }
            }

//setlocale(LC_CTYPE, 'cs_CZ');
//$row_name = iconv('UTF-8', 'ASCII//TRANSLIT', $row['name']);
            $pdf->CreateTextBox($row['name'], $x * 38 + 7 + $x * 1.5, $y * 22.0 + 5);
            switch ($par_Type) {
                case 'UPC':
                    $pdf->CreateBarcode($row['reg'], $x * 38 + 7 + $x * 1.5, $y * 22.0 + 10 + 5, 38, 11.5);
                    break;
                case 'QR':
                    $pdf->CreateQRCode($row['reg'], $x * 38 + 7 + $x * 1.5, $y * 22.0 + 10 + 5, 38, 11.5);
                    break;
            }
            $x++;
        }

        $pdf->Output('print_items.pdf', 'D');
    }

    function unset_all() {
        unset($_SESSION['print_items']);
        unset($_SESSION['selected_items']);
        unset($_SESSION['selected_partner']);

        return $this->generateItems();
    }

    function generateItems() {

//$blueticket = new blueticket_forms();

        $blueticket = blueticket_forms::get_instance();

        echo '<script type="text/javascript">
            function click_item(par_regnum, par_name, par_price, par_purchase_price) {
            $("#reg").val(par_regnum);
            $("#name").val(par_name);
            $("#price").val(par_price);
            $("#purchase_price").val(par_purchase_price);
$("#dialog").dialog("open");
};

$(document).ready(function() {
$(function() {
$("#dialog").dialog({
autoOpen: false,
width: 600
});
});

// Validating Form Fields.....
$("#count_form").submit(function(e) {
{
                e.preventDefault();
                $.ajax({
                    url: "add_print_item.php?cnt=" + $("#cnt").val() + "&reg=" + $("#reg").val() + "&name=" + $("#name").val() + "&price=" + $("#price").val() + "&purchase_price=" + $("#purchase_price").val(),
                }).done(function () {
$("#selected_data").append("<tr><td>" + $("#cnt").val() + "</td><td>" + $("#reg").val() + "</td><td>" + $("#name").val() + "</td></tr>");
$("#cnt").val("");
$("#dialog").dialog("close");
});
}
});
});
</script>';

        echo '
<div id="dialog" title="Dialog Form" style="width:600px">
<form action="" method="post" id="count_form" autocomplete="off">
<label>' . $this->getTranslatedText("Count") . ':</label>
<input id="cnt" name="cnt" type="text">
<label>' . $this->getTranslatedText("Price") . ':</label>
<input id="price" name="price" type="text">
<label>' . $this->getTranslatedText("PurchasePrice") . ':</label>
<input id="purchase_price" name="purchase_price" type="text">
<label>' . $this->getTranslatedText("RegistrationNumber") . ':</label>
<input id="reg" name="reg" type="text" disabled>
<label>' . $this->getTranslatedText("Name") . ':</label>
<input id="name" name="name" type="text" disabled>
<input id="submit" type="submit" value="Submit">
</form>
<table id="selected_data" style="width:600px">
<tr>
<td>' . $this->getTranslatedText("Qty") . '</td>
<td>' . $this->getTranslatedText("RegistrationNumber") . '</td>
<td>' . $this->getTranslatedText("Name") . '</td>
</tr>
</table>
</div>
';

        echo '<a href="?report=print_items_bc" class="btn btn-primary" style="width:150px; height:30px; margin-top:10px; margin-left:10px">' . $this->getTranslatedText('Tlač štítkov UPC') . '</a>';
        echo '<a href="?report=print_items_qr" class="btn btn-primary" style="width:150px; height:30px; margin-top:10px; margin-left:10px">' . $this->getTranslatedText('Tlač štítkov QR') . '</a>';
//        echo '<a href="?report=print_shipment" class="btn btn-primary" style="width:150px; height:30px; margin-top:10px; margin-left:10px">' . $this->getTranslatedText('Tlač výdajky') . '</a>';
//        echo '<a href="?report=print_purchase" class="btn btn-primary" style="width:150px; height:30px; margin-top:10px; margin-left:10px">' . $this->getTranslatedText('Tlač príjemky') . '</a>';
//        echo '<a href="?report=print_shipment_cash" class="btn btn-primary" style="width:150px; height:30px; margin-top:10px; margin-left:10px">' . $this->getTranslatedText('Tlač výdajky + kasa') . '</a>';
//        echo '<a href="?report=print_purchase_cash" class="btn btn-primary" style="width:150px; height:30px; margin-top:10px; margin-left:10px">' . $this->getTranslatedText('Tlač príjemky + kasa') . '</a>';
        echo '<a href="?report=unset_all" class="btn btn-primary" style="width:150px; height:30px; margin-top:10px; margin-left:10px">' . $this->getTranslatedText('Vyčistit') . '</a>';

        $blueticket->table('items'); //nazov tabulky v databaze
        $blueticket->order_by('Name', 'ASC');
        $blueticket->table_name($this->getTranslatedText('Items')); //titulok zobrazenia tabulky na stranke

        $blueticket->columns('Barcode,PLU,RegistrationNumber,Name,Qty,UnitID,Price,GroupID,SubtotalPrice,SubtotalPurchasePrice'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->fields('Barcode,PLU,Name,Qty,UnitID,Price,MinimalPrice,PurchasePrice,TaxID,GroupID,TypeID'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni

        $blueticket->label('PLU', $this->getTranslatedText('PLU'));
        $blueticket->label('RegistrationNumber', $this->getTranslatedText('RegistrationNumber'));
        $blueticket->label('Name', $this->getTranslatedText('Name'));
        $blueticket->label('Qty', $this->getTranslatedText('Qty'));
        $blueticket->label('UnitID', $this->getTranslatedText('UnitID'));
        $blueticket->label('Price', $this->getTranslatedText('Price'));
        $blueticket->label('MinimalPrice', $this->getTranslatedText('MinimalPrice'));
        $blueticket->label('PurchasePrice', $this->getTranslatedText('PurchasePrice'));
        $blueticket->label('TaxID', $this->getTranslatedText('TaxID'));
        $blueticket->label('GroupID', $this->getTranslatedText('GroupID'));
        $blueticket->label('Barcode', $this->getTranslatedText('Barcode'));
        $blueticket->label('TypeID', $this->getTranslatedText('TypeID'));

        $blueticket->column_pattern('Barcode', '<img style="width:90px; height:90px" src="inc/qrcode.php?code={RegistrationNumber}"/>');

        $blueticket->relation('UnitID', 'units', 'ID', 'Name');
        $blueticket->relation('TaxID', 'taxes', 'ID', 'Value');
        $blueticket->relation('GroupID', 'groups', 'ID', 'Name');
        $blueticket->relation('TypeID', 'item_types', 'ID', 'Name');

        $blueticket->subselect('SubtotalPrice', '{Price}*{Qty}');
        $blueticket->subselect('SubtotalPurchasePrice', '{PurchasePrice}*{Qty}');

        $blueticket->button("javascript:click_item('{RegistrationNumber}','{Name}','{Price}','{PurchasePrice}');", $this->getTranslatedText('Item labels'), 'glyphicon glyphicon-ok');

        $blueticket->highlight_row('PurchasePrice', '<', '{Price}', 'GreenYellow');
        $blueticket->highlight_row('PurchasePrice', '=', '{Price}', 'Yellow');
        $blueticket->highlight_row('PurchasePrice', '>', '{Price}', 'Orange');

        $blueticket->sum('SubtotalPrice, SubtotalPurchasePrice'); //  Zosumarizuje zvolene stlpce - berie do uvahy vsetky riadky filtrovanej tabulky

        $blueticket->label('SubtotalPrice', $this->getTranslatedText('SubtotalPrice'));
        $blueticket->label('SubtotalPurchasePrice', $this->getTranslatedText('SubtotalPurchasePrice'));

        $blueticket->default_tab($this->getTranslatedText('Items'));

        $blueticket->column_class('Qty,Price,MinimalPrice,PurchasePrice,SubtotalPrice,SubtotalPurchasePrice,SellToday,SellHistory', 'align-right');
        $blueticket->change_type('Price,MinimalPrice,PurchasePrice,SubtotalPrice,SubtotalPurchasePrice,SellToday,SellHistory', 'price', '0');
        $blueticket->before_insert('before_items_insert_callback', 'blueticket.pos.functions.php');


        $bt_content = $blueticket->nested_table($this->getTranslatedText('ItemsContent'), 'RegistrationNumber', 'items_content', 'RegistrationNumber');
        $bt_content->label('RegistrationNumber', $this->getTranslatedText('RegistrationNumber'));
        $bt_content->label('ContentFrom', $this->getTranslatedText('ContentFrom'));
        $bt_content->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_content->relation("ContentFrom", "items", "RegistrationNumber", "Name");
        $bt_content->change_type('Quantity', 'price', '0');


// invoices items month nested table
        $bt_item_invoice = $blueticket->nested_table($this->getTranslatedText('InvoicesItems'), 'RegistrationNumber', 'invoices_items', 'Barcode');
        $bt_item_invoice->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, CartNr, Group, Quantity, Price, SubTotal');
        //$bt_item_invoice->subselect('InvoiceDateTime', 'SELECT MAX(InvoiceDateTime) as InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');
        $bt_item_invoice->order_by('InvoiceDateTime', 'DESC');
        $bt_item_invoice->subselect('SubTotal', '{Price}*{Quantity}');
        $bt_item_invoice->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice->change_type('Quantity,Price,SubTotal', 'price', '0');
        $bt_item_invoice->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice->relation("Group", "groups", "ID", "Name");

        $bt_item_invoice->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $bt_item_invoice->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice->label('Group', $this->getTranslatedText('GroupID'));

        $bt_item_invoice->sum('SubTotal');


// invoices items month nested table
        $bt_item_invoice_month = $blueticket->nested_table($this->getTranslatedText('InvoicesItemsMonth'), 'RegistrationNumber', 'invoices_items_month', 'Barcode');
        $bt_item_invoice_month->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, CartNr, Quantity, Price, SubTotal');
        //$bt_item_invoice_month->subselect('InvoiceDateTime', 'SELECT MAX(InvoiceDateTime) as InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');
        $bt_item_invoice_month->order_by('InvoiceDateTime', 'DESC');
        $bt_item_invoice_month->subselect('SubTotal', '{Price}*{Quantity}');

        $bt_item_invoice_month->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $bt_item_invoice_month->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice_month->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice_month->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice_month->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice_month->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice_month->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice_month->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice_month->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice_month->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice_month->change_type('Quantity,Price,SubTotal', 'price', '0');
        $bt_item_invoice_month->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice_month->relation("Group", "groups", "ID", "Name");

        $bt_item_invoice_month->sum('SubTotal');


        return $blueticket->render();
    }

    function generatePartners() {
        $blueticket = blueticket_forms::get_instance();

        echo '<script type="text/javascript">
            function click_partner(par_id) {
                $.ajax({
                    url: "add_print_partner.php?id=" + par_id,
                }).done(function () {
                alert("Partner ID: " + par_id + " bol úspešne zvolený");
});
}
</script>';

        $blueticket->table('partners');
        $blueticket->table_name($this->getTranslatedText('Partners'));
        $blueticket->default_tab($this->getTranslatedText('Partners'));
        $blueticket->columns('Code, Name, Address, City, ZIP, PID, VAT, VATID'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni

        $blueticket->fields('Code, Name, Address, City, ZIP, State, PID, VAT, VATID, IBAN, SWIFT, Bank, Description'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni

        $blueticket->label('Code', $this->getTranslatedText('Code'));
        $blueticket->label('Name', $this->getTranslatedText('Name'));
        $blueticket->label('Address', $this->getTranslatedText('Address'));
        $blueticket->label('City', $this->getTranslatedText('City'));
        $blueticket->label('ZIP', $this->getTranslatedText('ZIP'));
        $blueticket->label('State', $this->getTranslatedText('State'));
        $blueticket->label('PID', $this->getTranslatedText('PID'));
        $blueticket->label('VAT', $this->getTranslatedText('VAT'));
        $blueticket->label('VATID', $this->getTranslatedText('VATID'));
        $blueticket->label('IBAN', $this->getTranslatedText('IBAN'));
        $blueticket->label('SWIFT', $this->getTranslatedText('SWIFT'));
        $blueticket->label('Bank', $this->getTranslatedText('Bank'));
        $blueticket->label('Description', $this->getTranslatedText('Description'));

        $blueticket->button("javascript:click_partner('{ID}');", $this->getTranslatedText('Item labels'), 'glyphicon glyphicon-ok');

        $bt_item_invoice = $blueticket->nested_table($this->getTranslatedText('InvoicesItems'), 'Code', 'invoices_items', 'CartNr');
        $bt_item_invoice->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice->subselect('SubTotal', '{Price}*{Quantity}');
        $bt_item_invoice->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice->relation("Group", "groups", "ID", "Name");

        $bt_item_invoice->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $bt_item_invoice->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice->change_type('Quantity,Price,SubTotal', 'price', '0');
        //$bt_item_invoice->subselect('InvoiceDateTime', 'SELECT InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');

        $bt_item_invoice->sum('SubTotal');

        $bt_item_invoice_month = $blueticket->nested_table($this->getTranslatedText('InvoicesItemsMonth'), 'Code', 'invoices_items_month', 'CartNr');
        $bt_item_invoice_month->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice_month->subselect('SubTotal', '{Price}*{Quantity}');
        $bt_item_invoice_month->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice_month->relation("Group", "groups", "ID", "Name");

        $bt_item_invoice_month->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $bt_item_invoice_month->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice_month->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice_month->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice_month->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice_month->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice_month->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice_month->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice_month->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice_month->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice_month->change_type('Quantity,Price,SubTotal', 'price', '0');
        //$bt_item_invoice_month->subselect('InvoiceDateTime', 'SELECT InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');

        $bt_item_invoice_month->sum('SubTotal');

        return $blueticket->render();
    }

    function generateTypes() {
        $blueticket_types = blueticket_forms::get_instance();
        $blueticket_types->table('document_types');
        $blueticket_types->table_name($this->getTranslatedText('DocumentTypes'));
        $blueticket_types->default_tab($this->getTranslatedText('DocumentTypes'));

        $blueticket_types->columns('Description, Counting, PrinterAddress, WHSign'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket_types->fields('Description, Counting, PrinterAddress, WHSign'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni

        $blueticket_types->label('Description', $this->getTranslatedText('Description'));
        $blueticket_types->label('Counting', $this->getTranslatedText('Counting'));
        $blueticket_types->label('PrinterAddress', $this->getTranslatedText('PrinterAddress'));
        $blueticket_types->label('WHSign', $this->getTranslatedText('WHSign'));

        return $blueticket_types->render();
    }

    function generateUnits() {
        $blueticket_units = blueticket_forms::get_instance();
        $blueticket_units->table('units');
        $blueticket_units->table_name($this->getTranslatedText('units'));
        $blueticket_units->default_tab($this->getTranslatedText('units'));

        $blueticket_units->columns('Name'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket_units->fields('Name'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket_units->label('Name', $this->getTranslatedText('Name'));

        return $blueticket_units->render();
    }

    function generateTypesDocuments() {
        echo '<script type="text/javascript">
            function print_doc(par_documentno) {
                $.ajax({
                    url: "printdoc.php?document=" + par_documentno,
                }).done(function () {
                alert("Doklad " + par_documentno + " bol odoslany do tlaciarne");
                });
            }
            function print_doc_pdf(par_documentno) {
                var url = "printdocpdf.php?document=" + par_documentno;
                window.open(url);
                //jQuery.fileDownload(url);
            }
            </script>';
        $blueticket_types = blueticket_forms::get_instance();

        $blueticket_types->table('document_types');
        $blueticket_types->table_name($this->getTranslatedText('DocumentTypes'));
        $blueticket_types->default_tab($this->getTranslatedText('DocumentTypes'));

        $blueticket_types->columns('Description'); //, PrinterAddress'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket_types->fields('Description'); //, PrinterAddress'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni

        $blueticket_types->label('Description', $this->getTranslatedText('Description'));
        $blueticket_types->label('PrinterAddress', $this->getTranslatedText('PrinterAddress'));

        $blueticket_types->disabled('Description,PrinterAddress');

        $blueticket = $blueticket_types->nested_table($this->getTranslatedText('Invoices'), 'ID', 'invoices', 'TypeID');
//        $blueticket->table_name($this->getTranslatedText('Invoices'));
        $blueticket->default_tab($this->getTranslatedText('Invoices'));

        $blueticket->columns('Partner, InvoiceDateTime, InvoiceNumber, UserName, InvoiceTotal, URL'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->fields('Partner, TypeID, CustomerID, CustomerDescription, InvoiceDateTime, PaymentTypeID, DeliveryTypeID'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->label('TypeID', $this->getTranslatedText('TypeID'));
        $blueticket->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $blueticket->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $blueticket->label('UserID', $this->getTranslatedText('UserID'));
        $blueticket->label('InvoiceTotal', $this->getTranslatedText('InvoiceTotal'));
//$blueticket->label('InvoiceTotalToday', $this->getTranslatedText('InvoiceTotalToday'));
        $blueticket->label('Partner', $this->getTranslatedText('Partner'));
        $blueticket->label('CustomerID', $this->getTranslatedText('CustomerID'));
        $blueticket->label('CustomerDescription', $this->getTranslatedText('CustomerDescription'));
        $blueticket->label('PaymentTypeID', $this->getTranslatedText('PaymentTypeID'));
        $blueticket->label('DeliveryTypeID', $this->getTranslatedText('DeliveryTypeID'));

        $blueticket->relation('TypeID', 'document_types', 'ID', 'Description');
        $blueticket->relation('PaymentTypeID', 'payment_types', 'ID', 'PaymentTypeName');
        $blueticket->relation('DeliveryTypeID', 'delivery_types', 'ID', 'Description');

        $blueticket->button("javascript:print_doc('{InvoiceNumber}');", $this->getTranslatedText('Print'), 'glyphicon glyphicon-print');
        $blueticket->button("javascript:print_doc_pdf('{InvoiceNumber}');", $this->getTranslatedText('Print'), 'glyphicon glyphicon-print');

        $blueticket->subselect('UserName', 'SELECT Username FROM users WHERE ID={UserID}');
        $blueticket->subselect('Partner', "SELECT Name FROM partners WHERE partners.ID={CustomerID}");
        $blueticket->subselect('URL', "SELECT 'http://localhost/blueticket.forms/printdocpdf.php?document={InvoiceNumber}'");
        $blueticket->subselect('InvoiceTotal', 'SELECT SUM(Price*Quantity) as InvoiceTotal FROM invoices_items WHERE InvoiceNumber={InvoiceNumber}');
//$blueticket->subselect('InvoiceTotalToday', 'SELECT SUM(Price*Quantity) as InvoiceTotal FROM invoices_items WHERE InvoiceNumber={InvoiceNumber}');
        $blueticket->order_by('InvoiceDateTime', 'DESC');

        $blueticket->change_type('InvoiceTotal', 'price', '0');
        $blueticket->column_class('InvoiceTotal', 'align-right');
        $blueticket->sum('InvoiceTotal');

        $blueticket->set_attr('CustomerID', array('id' => 'customerid'));

        $blueticket->no_editor('CustomerDescription');
        $blueticket->change_type('CustomerDescription', 'textarea', '', array('style' => 'height:250px'));
        $blueticket->set_attr('CustomerDescription', array('id' => 'customerdesc'));
        $blueticket->before_insert('before_document_insert_callback', 'blueticket.pos.functions.php');
// invoices items month nested table
        $bt_item_invoice = $blueticket->nested_table($this->getTranslatedText('InvoicesItems'), 'InvoiceNumber', 'invoices_items', 'InvoiceNumber');
        $bt_item_invoice->columns('InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice->fields('InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, TAXPercent, Unit, SubTotal');

        $bt_item_invoice->set_attr('Name', array('id' => 'name'));
        $bt_item_invoice->set_attr('Barcode', array('id' => 'regnum'));
        $bt_item_invoice->set_attr('TAXPercent', array('id' => 'taxpercent'));
        $bt_item_invoice->set_attr('Unit', array('id' => 'unit'));

        $bt_item_invoice->disabled('InvoiceNumber');

        $bt_item_invoice->subselect('SubTotal', '{Price}*{Quantity}');

        $bt_item_invoice->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice->label('TAXPercent', $this->getTranslatedText('TAXPercent'));
        $bt_item_invoice->label('Unit', $this->getTranslatedText('Unit'));
        $bt_item_invoice->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice->relation("Group", "groups", "ID", "Name");
        $bt_item_invoice->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice->change_type('Quantity,Price,SubTotal', 'price', '0');

        $bt_item_invoice->before_insert('before_document_item_insert_callback', 'blueticket.pos.functions.php');
        $bt_item_invoice->before_remove('before_document_item_delete_callback', 'blueticket.pos.functions.php');
        $bt_item_invoice->set_attr('Price', array('id' => 'price'));

        $bt_item_invoice->sum('SubTotal');

        $bt_item_invoice_month = $blueticket->nested_table($this->getTranslatedText('InvoicesItemsMonth'), 'InvoiceNumber', 'invoices_items_month', 'InvoiceNumber');
        $bt_item_invoice_month->columns('InvoiceNumber, Barcode, Name, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice_month->subselect('SubTotal', '{Price}*{Quantity}');

        $bt_item_invoice_month->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice_month->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice_month->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice_month->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice_month->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice_month->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice_month->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice->relation("Group", "groups", "ID", "Name");
        $bt_item_invoice_month->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice_month->change_type('Quantity,Price,SubTotal', 'price', '0');

        $bt_item_invoice_month->sum('SubTotal');

        return $blueticket_types->render();
    }

    function generateInvoices() {
        $blueticket = blueticket_forms::get_instance();
        $blueticket->table('invoices');
        $blueticket->table_name($this->getTranslatedText('Invoices'));
        $blueticket->default_tab($this->getTranslatedText('Invoices'));

        $blueticket->columns('Partner, InvoiceDateTime, InvoiceNumber, UserName, InvoiceTotal,InvoiceTotalToday'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->fields('CustomerID, CustomerDescription, InvoiceDateTime'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $blueticket->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $blueticket->label('UserID', $this->getTranslatedText('UserID'));
        $blueticket->label('InvoiceTotal', $this->getTranslatedText('InvoiceTotal'));
        $blueticket->label('InvoiceTotalToday', $this->getTranslatedText('InvoiceTotalToday'));
        $blueticket->label('Partner', $this->getTranslatedText('Partner'));
        $blueticket->label('CustomerID', $this->getTranslatedText('CustomerID'));
        $blueticket->label('CustomerDescription', $this->getTranslatedText('CustomerDescription'));

        $blueticket->subselect('UserName', 'SELECT Username FROM users WHERE ID={UserID}');
        $blueticket->subselect('Partner', 'SELECT Name FROM partners WHERE ID=(SELECT MAX(CartNr) FROM invoices_items WHERE invoices_items.InvoiceNumber={InvoiceNumber})');

        $blueticket->subselect('InvoiceTotal', 'SELECT SUM(Price*Quantity) as InvoiceTotal FROM invoices_items_month WHERE InvoiceNumber={InvoiceNumber} AND Barcode!=0');
        $blueticket->subselect('InvoiceTotalToday', 'SELECT SUM(Price*Quantity) as InvoiceTotal FROM invoices_items WHERE InvoiceNumber={InvoiceNumber} AND Barcode!=0');
        $blueticket->order_by('InvoiceDateTime', 'DESC');

        $blueticket->change_type('InvoiceTotal,InvoiceTotalToday', 'price', '0');
        $blueticket->column_class('InvoiceTotal,InvoiceTotalToday', 'align-right');
        $blueticket->sum('InvoiceTotal,InvoiceTotalToday');

        $blueticket->set_attr('CustomerID', array('id' => 'customerid'));
        $blueticket->no_editor('CustomerDescription');
        $blueticket->change_type('CustomerDescription', 'textarea', '', array('style' => 'height:250px'));
        $blueticket->set_attr('CustomerDescription', array('id' => 'customerdesc'));
        //$blueticket->before_create('before_document_create_callback', 'blueticket.pos.functions.php');
// invoices items month nested table
        $bt_item_invoice = $blueticket->nested_table($this->getTranslatedText('InvoicesItems'), 'InvoiceNumber', 'invoices_items', 'InvoiceNumber');
        $bt_item_invoice->columns('InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice->subselect('SubTotal', '{Price}*{Quantity}');

        $bt_item_invoice->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice->relation("Group", "groups", "ID", "Name");
        $bt_item_invoice->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice->change_type('Quantity,Price,SubTotal', 'price', '0');

        $bt_item_invoice->sum('SubTotal');

        $bt_item_invoice_month = $blueticket->nested_table($this->getTranslatedText('InvoicesItemsMonth'), 'InvoiceNumber', 'invoices_items_month', 'InvoiceNumber');
        $bt_item_invoice_month->columns('InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice_month->subselect('SubTotal', '{Price}*{Quantity}');

        $bt_item_invoice_month->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice_month->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice_month->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice_month->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice_month->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice_month->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice_month->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice_month->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice_month->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice_month->relation("Group", "groups", "ID", "Name");
        $bt_item_invoice_month->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice_month->change_type('Quantity,Price,SubTotal', 'price', '0');

        $bt_item_invoice_month->sum('SubTotal');

        return $blueticket->render();
    }

    function generateTranslate() {
        $blueticket = blueticket_forms::get_instance();

        $blueticket->table('translate');
        $blueticket->table_name($this->getTranslatedText('Translate'));

        $blueticket->no_editor("TextToTranslate, TranslatedText, Lang");

        return $blueticket->render();
    }

    function generateUsers() {
        $blueticket_u = blueticket_forms::get_instance();

        $blueticket_u->table('users');
        $blueticket_u->table_name($this->getTranslatedText('Users'));
        $blueticket_u->default_tab($this->getTranslatedText('Users'));

        $blueticket_u->columns("Username, Password");
        $blueticket_u->fields("Username, Password");

        $blueticket = $blueticket_u->nested_table($this->getTranslatedText('Invoices'), 'ID', 'invoices', 'UserID');
        $blueticket->table('invoices');
        $blueticket->table_name($this->getTranslatedText('Invoices'));
        $blueticket->default_tab($this->getTranslatedText('Invoices'));

        $blueticket->columns('Partner, InvoiceDateTime, InvoiceNumber, UserName, InvoiceTotal,InvoiceTotalToday'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->fields('CustomerID, CustomerDescription, InvoiceDateTime'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $blueticket->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $blueticket->label('UserID', $this->getTranslatedText('UserID'));
        $blueticket->label('InvoiceTotal', $this->getTranslatedText('InvoiceTotal'));
        $blueticket->label('InvoiceTotalToday', $this->getTranslatedText('InvoiceTotalToday'));
        $blueticket->label('Partner', $this->getTranslatedText('Partner'));
        $blueticket->label('CustomerID', $this->getTranslatedText('CustomerID'));
        $blueticket->label('CustomerDescription', $this->getTranslatedText('CustomerDescription'));

        $blueticket->subselect('UserName', 'SELECT Username FROM users WHERE ID={UserID}');
        $blueticket->subselect('Partner', 'SELECT Name FROM partners WHERE ID=(SELECT MAX(CartNr) FROM invoices_items WHERE invoices_items.InvoiceNumber={InvoiceNumber})');

        $blueticket->subselect('InvoiceTotal', 'SELECT SUM(Price*Quantity) as InvoiceTotal FROM invoices_items_month WHERE InvoiceNumber={InvoiceNumber} AND Barcode!=0');
        $blueticket->subselect('InvoiceTotalToday', 'SELECT SUM(Price*Quantity) as InvoiceTotal FROM invoices_items WHERE InvoiceNumber={InvoiceNumber} AND Barcode!=0');
        $blueticket->order_by('InvoiceDateTime', 'DESC');

        $blueticket->change_type('InvoiceTotal,InvoiceTotalToday', 'price', '0');
        $blueticket->column_class('InvoiceTotal,InvoiceTotalToday', 'align-right');
        $blueticket->sum('InvoiceTotal,InvoiceTotalToday');

        $blueticket->set_attr('CustomerID', array('id' => 'customerid'));
        $blueticket->no_editor('CustomerDescription');
        $blueticket->change_type('CustomerDescription', 'textarea', '', array('style' => 'height:250px'));
        $blueticket->set_attr('CustomerDescription', array('id' => 'customerdesc'));
        //$blueticket->before_create('before_document_create_callback', 'blueticket.pos.functions.php');
// invoices items month nested table
        $bt_item_invoice = $blueticket->nested_table($this->getTranslatedText('InvoicesItems'), 'InvoiceNumber', 'invoices_items', 'InvoiceNumber');
        $bt_item_invoice->columns('InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice->subselect('SubTotal', '{Price}*{Quantity}');

        $bt_item_invoice->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice->relation("Group", "groups", "ID", "Name");
        $bt_item_invoice->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice->change_type('Quantity,Price,SubTotal', 'price', '0');

        $bt_item_invoice->sum('SubTotal');

        $bt_item_invoice_month = $blueticket->nested_table($this->getTranslatedText('InvoicesItemsMonth'), 'InvoiceNumber', 'invoices_items_month', 'InvoiceNumber');
        $bt_item_invoice_month->columns('InvoiceNumber, Barcode, Name, Group, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice_month->subselect('SubTotal', '{Price}*{Quantity}');

        $bt_item_invoice_month->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice_month->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice_month->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice_month->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice_month->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice_month->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice_month->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice_month->label('Group', $this->getTranslatedText('GroupID'));
        $bt_item_invoice_month->change_type('InvoiceDateTime', 'datetime');
        $bt_item_invoice_month->relation("Group", "groups", "ID", "Name");
        $bt_item_invoice_month->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice_month->change_type('Quantity,Price,SubTotal', 'price', '0');

        $bt_item_invoice_month->sum('SubTotal');


        return $blueticket_u->render();
    }

}
