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

require_once ('tcpdf/tcpdf_barcodes_2d.php');

class blueticket_objects {

    public $lang = 'sk';

    function __construct() {
        
    }

    function getTranslatedText($par_Text, $par_lang = 'en') {
        $par_lang = $this->lang;

        $blueticket_db = blueticket_forms_db::get_instance();

        $blueticket_db->query("CREATE TABLE IF NOT EXISTS translate (ID BIGINT NOT NULL AUTO_INCREMENT,TextToTranslate TEXT NULL,TranslatedText TEXT NULL,Lang TEXT NULL,PRIMARY KEY (ID)) ENGINE=MyISAM;");

        $blueticket_db->query("SELECT TranslatedText FROM translate WHERE TextToTranslate='$par_Text' AND Lang='$par_lang'");
        $myrow = $blueticket_db->row();

        if (!$myrow) {
            $blueticket_db->query("INSERT INTO translate(TextToTranslate,TranslatedText,Lang) VALUES('$par_Text','$par_Text','$par_lang')");
            $par_Text = $par_Text;
        } else {
            $par_Text = $myrow['TranslatedText'];
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

    function generateItems() {

        //$blueticket = new blueticket_forms();

        $blueticket = blueticket_forms::get_instance();
        $blueticket->table('items'); //nazov tabulky v databaze
        $blueticket->order_by('Name', 'ASC');
        $blueticket->table_name($this->getTranslatedText('Items')); //titulok zobrazenia tabulky na stranke

        $blueticket->columns('Barcode,PLU,RegistrationNumber,Name,Qty,UnitID,Price,MinimalPrice,PurchasePrice,TaxID,GroupID,Barcode,SubtotalPrice,SubtotalPurchasePrice,SellToday,SellHistory'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
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
        $blueticket->label('SubtotalPrice', $this->getTranslatedText('SubtotalPrice'));
        $blueticket->label('SubtotalPurchasePrice', $this->getTranslatedText('SubtotalPurchasePrice'));
        $blueticket->label('SellToday', $this->getTranslatedText('SellToday'));
        $blueticket->label('SellHistory', $this->getTranslatedText('SellHistory'));

        $blueticket->column_pattern('Barcode', '<img style="width:90px; height:40px" src="http://localhost/blueticket.forms/inc/qrcode.php?code={RegistrationNumber}"/>');
        
        $blueticket->relation('UnitID', 'units', 'ID', 'Name');
        $blueticket->relation('TaxID', 'taxes', 'ID', 'Value');
        $blueticket->relation('GroupID', 'groups', 'ID', 'Name');

        $blueticket->subselect('SubtotalPrice', '{Price}*{Qty}');
        $blueticket->subselect('SubtotalPurchasePrice', '{PurchasePrice}*{Qty}');
        $blueticket->subselect('SellToday', 'SELECT SUM(Quantity) as SellToday FROM invoices_items WHERE Barcode={RegistrationNumber}');
        $blueticket->subselect('SellHistory', 'SELECT SUM(Quantity) as SellHistory FROM invoices_items_month WHERE Barcode={RegistrationNumber}');

        $blueticket->button("javascript:alert('{RegistrationNumber}');", 'bticon');
        
        $blueticket->highlight_row('PurchasePrice', '<', '{Price}', 'GreenYellow');
        $blueticket->highlight_row('PurchasePrice', '=', '{Price}', 'Yellow');
        $blueticket->highlight_row('PurchasePrice', '>', '{Price}', 'Orange');

        $blueticket->sum('SubtotalPrice, SubtotalPurchasePrice'); //  Zosumarizuje zvolene stlpce - berie do uvahy vsetky riadky filtrovanej tabulky

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
        $blueticket->subselect('UserName','SELECT Username FROM users WHERE ID={UserID}');
        
        
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
    
    function generateTranslate()
    {
        $blueticket = blueticket_forms::get_instance();
        
        $blueticket->table('translate');
        $blueticket->table_name($this->getTranslatedText('Translate'));
        
        return $blueticket->render();
    }

}
