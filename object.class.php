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

class blueticket_objects {

    function __construct() {
        
    }

    function getTranslatedText($par_Text) {
        return $par_Text;
    }

    function generateItems() {

        //$blueticket = new blueticket_forms();

        $blueticket = blueticket_forms::get_instance();
        $blueticket->table('items'); //nazov tabulky v databaze
        $blueticket->order_by('Name', 'ASC');
        $blueticket->table_name($this->getTranslatedText('Items')); //titulok zobrazenia tabulky na stranke

        $blueticket->columns('PLU,RegistrationNumber,Name,Qty,UnitID,Price,MinimalPrice,PurchasePrice,TaxID,GroupID,Barcode,SubtotalPrice,SubtotalPurchasePrice,SellToday,SellHistory'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
        $blueticket->fields('PLU,Name,Qty,UnitID,Price,MinimalPrice,PurchasePrice,TaxID,GroupID,Barcode'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni

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

        $blueticket->relation('UnitID', 'units', 'ID', 'Name');
        $blueticket->relation('TaxID', 'taxes', 'ID', 'Value');
        $blueticket->relation('GroupID', 'groups', 'ID', 'Name');

        $blueticket->subselect('SubtotalPrice', '{Price}*{Qty}');
        $blueticket->subselect('SubtotalPurchasePrice', '{PurchasePrice}*{Qty}');
        $blueticket->subselect('SellToday', 'SELECT SUM(Quantity) as SellToday FROM invoices_items WHERE Barcode={RegistrationNumber}');
        $blueticket->subselect('SellHistory', 'SELECT SUM(Quantity) as SellHistory FROM invoices_items_month WHERE Barcode={RegistrationNumber}');

        $blueticket->highlight_row('PurchasePrice', '<', '{Price}', 'GreenYellow');
        $blueticket->highlight_row('PurchasePrice', '=', '{Price}', 'Yellow');
        $blueticket->highlight_row('PurchasePrice', '>', '{Price}', 'Orange');

        $blueticket->sum('SubtotalPrice, SubtotalPurchasePrice'); //  Zosumarizuje zvolene stlpce - berie do uvahy vsetky riadky filtrovanej tabulky

        //$bt_item_invoice_month = new blueticket_forms();

        $blueticket->default_tab($this->getTranslatedText('Items'));
        
        $blueticket->column_class('Qty,Price,MinimalPrice,PurchasePrice,SubtotalPrice,SubtotalPurchasePrice', 'align-right');
        $blueticket->change_type('Price,MinimalPrice,PurchasePrice,SubtotalPrice,SubtotalPurchasePrice', 'price', '0', array('suffix' => ' €'));
        $blueticket->before_insert('before_items_insert_callback', 'blueticket.pos.functions.php');

        // invoices items month nested table
        $bt_item_invoice = $blueticket->nested_table('InvoicesItems', 'RegistrationNumber', 'invoices_items', 'Barcode');
        $bt_item_invoice->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, CartNr');
        $bt_item_invoice->subselect('InvoiceDateTime', 'SELECT MAX(InvoiceDateTime) as InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');
        $bt_item_invoice->order_by('InvoiceDateTime', 'DESC');

        // invoices items month nested table
        $bt_item_invoice_month = $blueticket->nested_table('InvoicesItemsMonth', 'RegistrationNumber', 'invoices_items_month', 'Barcode');
        $bt_item_invoice_month->columns('InvoiceDateTime, InvoiceNumber, Barcode, Name, CartNr');
        $bt_item_invoice_month->subselect('InvoiceDateTime', 'SELECT MAX(InvoiceDateTime) as InvoiceDateTime FROM invoices WHERE InvoiceNumber={InvoiceNumber}');
        $bt_item_invoice_month->order_by('InvoiceDateTime', 'DESC');


        return $blueticket->render();
    }

}
