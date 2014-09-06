<?php
error_reporting(E_ALL);
require_once ('forms/blueticket_forms.php');

$blueticket = blueticket_forms::get_instance();
?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php

//master tabulka - zoznam blockov
$blueticket->table('invoices'); //nazov tabulky v databaze
$blueticket->order_by('InvoiceDateTime', 'desc');
$blueticket->table_name('Bills'); //titulok zobrazenia tabulky na stranke

$blueticket->default_tab('Bills'); //nastavenia predvoleneho zobrazeneho tabu v detailovom zobrazeni tabulky

$blueticket->columns('InvoiceDateTime,InvoiceNumber,Sum,Currency,Desk,Obsluha,Storna'); //nastavenie stlpcov tabulky, ktore sa zobrazia v tabulkovom zobrazeni
$blueticket->column_class('InvoiceNumber,Sum,Storna', 'align-right'); //zmena stylu zobrazenia vybratych stlpcom

$blueticket->fields('InvoiceDateTime,InvoiceNumber,Sum,Desk,Obsluha,Storna'); //stlpce ktore sa zobrazia pri editacii a detailnom zobrazeni - automaticky sa pri editacii znepristupnia subselect-y

$blueticket->label('InvoiceDateTime', 'Date and Time of Bill');  //zmena labelov v zobrazeni fieldov
$blueticket->label('InvoiceNumber', 'Bill No.#'); //zmena labelov v zobrazeni fieldov
//$blueticket->field_tooltip("InvoiceDateTime", "This is tooltip"); //pridanie tooltipu pre field

$blueticket->subselect('Sum', 'SELECT SUM(Price*Quantity) FROM invoices_items WHERE InvoiceNumber = {InvoiceNumber} AND (Barcode != 0)'); // custom select stlpca z inej tabulky - virtualny stlpec
$blueticket->subselect('Desk', 'SELECT MAX(CartNr) FROM invoices_items WHERE InvoiceNumber = {InvoiceNumber}'); // custom select stlpca z inej tabulky - virtualny stlpec
$blueticket->subselect('Obsluha', 'SELECT MAX(Username) FROM users WHERE ID = {UserID}'); // custom select stlpca z inej tabulky - virtualny stlpec
$blueticket->subselect('Storna', 'SELECT SUM(ABS(Quantity)*Price) FROM invoices_items WHERE Quantity < 0 AND InvoiceNumber = {InvoiceNumber}'); // custom select stlpca z inej tabulky - virtualny stlpec

$blueticket->sum('Sum, Storna'); //  Zosumarizuje zvolene stlpce - berie do uvahy vsetky riadky filtrovanej tabulky
//nastavenia kriterii zvyraznovania
$blueticket->highlight_row('Sum', '<', 1, 'lightblue'); //nastavenie farby riadkov, podla zadaneho kriteria
$blueticket->highlight_row('Sum', '>=', 1, 'yellowgreen'); //nastavenie farby riadkov, podla zadaneho kriteria
$blueticket->highlight_row('Sum', '>=', 5, 'greenyellow'); //nastavenie farby riadkov, podla zadaneho kriteria
$blueticket->highlight_row('Storna', '>', 0, 'orange');
$blueticket->subselect('Currency', '"€"'); //pridanie stlpca s hodnotou
$blueticket->change_type('Sum', 'price', '0', ''); //number format
$blueticket->change_type('Storna', 'price', '0', ''); //number format
//nested tabulka - zoznam poloziek na blocku
$orderdetails = $blueticket->nested_table('Bill items', 'InvoiceNumber', 'invoices_items', 'InvoiceNumber'); // polozky - nested table
$orderdetails->columns('Barcode,Name,Price,Quantity,Sum,CartNr');
$orderdetails->fields('Barcode,Name,Price,Quantity,Sum,CartNr');

$orderdetails->label('Barcode', 'Registracne cislo');
$orderdetails->subselect('Sum', '{Price}*{Quantity}');
$orderdetails->highlight_row('Quantity', '=', 0, 'yellowgreen');
$orderdetails->highlight_row('Quantity', '>=', 0, 'greenyellow');
$orderdetails->highlight_row('Quantity', '<', 0, 'orange');
$orderdetails->change_type('Price,Sum', 'price', '0', array('suffix' => ' €')); // number format
$orderdetails->change_type('Quantity', 'price', '0', array('suffix' => ' ks')); // number format
$orderdetails->column_class('Barcode,Price,Quantity,Sum', 'align-right'); //change align
$orderdetails->sum('Quantity');
$orderdetails->sum('Sum');

echo $blueticket->render(); //final render of the table
?>
