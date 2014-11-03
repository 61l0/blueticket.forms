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

class InvoicePDF extends TCPDF {

    public function Header() {
        $this->SetY(15);
        $this->SetFont(PDF_FONT_NAME_MAIN, 'I', 8);
        $this->Cell(0, 10, 'FUNSTAR s.r.o.', 0, false, 'C');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont(PDF_FONT_NAME_MAIN, 'I', 8);
        $this->Cell(0, 10, 'Generované systémom blueticket™ (http://www.blueticket.eu)', 0, false, 'C');
    }

    public function CreateTextBox($textval, $x = 0, $y, $width = 0, $height = 10, $fontsize = 10, $fontstyle = '', $align = 'L') {
        $this->SetXY($x + 20, $y); // 20 = margin left
        $this->SetFont(PDF_FONT_NAME_MAIN, $fontstyle, $fontsize);
        $this->Cell($width, $height, $textval, 0, false, $align);
    }

    public function CreateInvoice() {
        // create a PDF object
        $pdf = new InvoicePDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document (meta) information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Olaf Lederer');
        $pdf->SetTitle('TCPDF Example');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, tutorial');

// add a page
        $pdf->AddPage();

// create address box
        $pdf->CreateTextBox('Customer name Inc.', 0, 55, 80, 10, 10, 'B');
        $pdf->CreateTextBox('Mr. Tom Cat', 0, 60, 80, 10, 10);
        $pdf->CreateTextBox('Street address', 0, 65, 80, 10, 10);
        $pdf->CreateTextBox('Zip, city name', 0, 70, 80, 10, 10);

// invoice title / number
        $pdf->CreateTextBox('Invoice #201012345', 0, 90, 120, 20, 16);

// date, order ref
        $pdf->CreateTextBox('Date: ' . date('Y-m-d'), 0, 100, 0, 10, 10, '', 'R');
        $pdf->CreateTextBox('Order ref.: #6765765', 0, 105, 0, 10, 10, '', 'R');


        //items
        // list headers
        $pdf->CreateTextBox('Quantity', 0, 120, 20, 10, 10, 'B', 'C');
        $pdf->CreateTextBox('Product or service', 20, 120, 90, 10, 10, 'B');
        $pdf->CreateTextBox('Price', 110, 120, 30, 10, 10, 'B', 'R');
        $pdf->CreateTextBox('Amount', 140, 120, 30, 10, 10, 'B', 'R');

        $pdf->Line(20, 129, 195, 129);

// some example data
        $orders[] = array('quant' => 5, 'descr' => '.com domain registration', 'price' => 9.95);
        $orders[] = array('quant' => 3, 'descr' => '.net domain name renewal', 'price' => 11.95);
        $orders[] = array('quant' => 1, 'descr' => 'SSL certificate 256-Byte encryption', 'price' => 99.95);
        $orders[] = array('quant' => 1, 'descr' => '25GB VPS Hosting, 200GB Bandwidth', 'price' => 19.95);

        $currY = 128;
        $total = 0;
        foreach ($_SESSION['selected_items'] as $row) {
            $pdf->CreateTextBox($row['qty'], 0, $currY, 20, 10, 10, '', 'C');
            $pdf->CreateTextBox($row['reg'] . ' - ' . $row['name'], 20, $currY, 90, 10, 10, '');
            $pdf->CreateTextBox(number_format($row['price'], 2, '.', ' ') . ' €', 110, $currY, 30, 10, 10, '', 'R');
            $amount = $row['qty'] * $row['price'];
            $pdf->CreateTextBox(number_format($amount, 2, '.', ' ') . ' €', 140, $currY, 30, 10, 10, '', 'R');
            $currY = $currY + 5;
            $total = $total + $amount;
        }
        $pdf->Line(20, $currY + 4, 195, $currY + 4);

        //footer
        //
        // output the total row
        $pdf->CreateTextBox('Total', 20, $currY + 5, 135, 10, 10, 'B', 'R');
        $pdf->CreateTextBox('$' . number_format($total, 2, '.', ''), 140, $currY + 5, 30, 10, 10, 'B', 'R');

// some payment instructions or information
        $pdf->setXY(20, $currY + 30);
        $pdf->SetFont(PDF_FONT_NAME_MAIN, '', 10);
        $pdf->MultiCell(175, 10, '<em>Lorem ipsum dolor sit amet, consectetur adipiscing elit</em>. 
Vestibulum sagittis venenatis urna, in pellentesque ipsum pulvinar eu. In nec <a href="http://www.google.com/">nulla libero</a>, eu sagittis diam. Aenean egestas pharetra urna, et tristique metus egestas nec. Aliquam erat volutpat. Fusce pretium dapibus tellus.', 0, 'L', 0, 1, '', '', true, null, true);

//Close and output PDF document
        $pdf->Output('invoice.pdf', 'D');
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

    function getTranslatedText($par_Text, $par_lang = 'sk') {
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

    function generateMenu() {
        $return = '<div style="width:100%; height:50px;padding-left:5px">';

        $return .= '<a href="?report=cards" class="btn btn-primary" style="width:150px; height:30px; margin-top:5px; margin-right:5px">Cenníky</a>';
        $return .= '<a href="?report=stats" class="btn btn-primary" style="width:150px; height:30px; margin-top:5px; margin-right:5px">Štatistika</a>';
        $return .= '<a href="?report=trans" class="btn btn-primary" style="width:150px; height:30px; margin-top:5px; margin-right:5px">Preklad</a>';

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
<form action="" method="post" id="count_form">
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

        echo '<a href="?report=print_items_bc" class="btn btn-primary" style="width:150px; height:30px; margin-top:10px; margin-left:10px">Tlač štítkov UPC</a>';
        echo '<a href="?report=print_items_qr" class="btn btn-primary" style="width:150px; height:30px; margin-top:10px; margin-left:10px">Tlač štítkov QR</a>';
        echo '<a href="?report=print_invoice" class="btn btn-primary" style="width:150px; height:30px; margin-top:10px; margin-left:10px">Tlač faktúry</a>';

        $blueticket->table('items'); //nazov tabulky v databaze
        $blueticket->order_by('Name', 'ASC');
        $blueticket->table_name($this->getTranslatedText('Items')); //titulok zobrazenia tabulky na stranke

        $blueticket->columns('PLU,RegistrationNumber,Name,Qty,UnitID,Price,MinimalPrice,PurchasePrice,TaxID,GroupID,SubtotalPrice,SubtotalPurchasePrice'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->fields('Barcode,PLU,Name,Qty,UnitID,Price,MinimalPrice,PurchasePrice,TaxID,GroupID,Barcode'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni

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

        $blueticket->column_pattern('Barcode', '<img style="width:90px; height:40px" src="http://localhost/blueticket.forms/inc/qrcode.php?code={RegistrationNumber}"/>');

        $blueticket->relation('UnitID', 'units', 'ID', 'Name');
        $blueticket->relation('TaxID', 'taxes', 'ID', 'Value');
        $blueticket->relation('GroupID', 'groups', 'ID', 'Name');

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

// invoices items month nested table
        $bt_item_invoice = $blueticket->nested_table($this->getTranslatedText('InvoicesItems'), 'RegistrationNumber', 'invoices_items', 'Barcode');
        $bt_item_invoice->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice->subselect('InvoiceDateTime', 'SELECT MAX(InvoiceDateTime) as InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');
        $bt_item_invoice->order_by('InvoiceDateTime', 'DESC');
        $bt_item_invoice->subselect('SubTotal', '{Price}*{Quantity}');
        $bt_item_invoice->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice->change_type('Quantity,Price,SubTotal', 'price', '0');

        $bt_item_invoice->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $bt_item_invoice->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice->label('SubTotal', $this->getTranslatedText('SubTotal'));

        $bt_item_invoice->sum('SubTotal');


// invoices items month nested table
        $bt_item_invoice_month = $blueticket->nested_table($this->getTranslatedText('InvoicesItemsMonth'), 'RegistrationNumber', 'invoices_items_month', 'Barcode');
        $bt_item_invoice_month->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice_month->subselect('InvoiceDateTime', 'SELECT MAX(InvoiceDateTime) as InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');
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
        $bt_item_invoice_month->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice_month->change_type('Quantity,Price,SubTotal', 'price', '0');

        $bt_item_invoice_month->sum('SubTotal');


        return $blueticket->render();
    }

    function generateInvoices() {
//$blueticket = new blueticket_forms();

        $blueticket = blueticket_forms::get_instance();

        $blueticket->table('invoices');
        $blueticket->table_name($this->getTranslatedText('Invoices'));
        $blueticket->default_tab($this->getTranslatedText('Invoices'));

        $blueticket->columns('InvoiceDateTime, InvoiceNumber, UserID, UserName, InvoiceTotal, InvoiceTotalToday'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->fields('InvoiceDateTime, InvoiceNumber, UserID, UserName, InvoiceTotal, InvoiceTotalToday'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->label('InvoiceDateTime', $this->getTranslatedText('InvoiceDateTime'));
        $blueticket->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $blueticket->label('UserID', $this->getTranslatedText('UserID'));
        $blueticket->label('InvoiceTotal', $this->getTranslatedText('InvoiceTotal'));
        $blueticket->label('InvoiceTotalToday', $this->getTranslatedText('InvoiceTotalToday'));
        $blueticket->subselect('UserName', 'SELECT Username FROM users WHERE ID={UserID}');


        $blueticket->subselect('InvoiceTotal', 'SELECT SUM(Price*Quantity) as InvoiceTotal FROM invoices_items_month WHERE InvoiceNumber={InvoiceNumber}');
        $blueticket->subselect('InvoiceTotalToday', 'SELECT SUM(Price*Quantity) as InvoiceTotal FROM invoices_items WHERE InvoiceNumber={InvoiceNumber}');
        $blueticket->order_by('InvoiceDateTime', 'DESC');

        $blueticket->change_type('InvoiceTotal,InvoiceTotalToday', 'price', '0');
        $blueticket->column_class('InvoiceTotal,InvoiceTotalToday', 'align-right');
        $blueticket->sum('InvoiceTotal,InvoiceTotalToday');

// invoices items month nested table
        $bt_item_invoice = $blueticket->nested_table($this->getTranslatedText('InvoicesItems'), 'InvoiceNumber', 'invoices_items', 'InvoiceNumber');
        $bt_item_invoice->columns('InvoiceNumber, Barcode, Name, CartNr, Quantity, Price, SubTotal');
        $bt_item_invoice->subselect('SubTotal', '{Price}*{Quantity}');

        $bt_item_invoice->label('InvoiceNumber', $this->getTranslatedText('InvoiceNumber'));
        $bt_item_invoice->label('Barcode', $this->getTranslatedText('Barcode'));
        $bt_item_invoice->label('Name', $this->getTranslatedText('Name'));
        $bt_item_invoice->label('CartNr', $this->getTranslatedText('CartNr'));
        $bt_item_invoice->label('Quantity', $this->getTranslatedText('Quantity'));
        $bt_item_invoice->label('Price', $this->getTranslatedText('Price'));
        $bt_item_invoice->label('SubTotal', $this->getTranslatedText('SubTotal'));
        $bt_item_invoice->column_class('Quantity,Price,SubTotal', 'align-right');
        $bt_item_invoice->change_type('Quantity,Price,SubTotal', 'price', '0');

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

}
