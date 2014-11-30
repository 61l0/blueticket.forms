<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function before_items_insert_callback($row_data, $primary) {
    //$blueticket_db = new blueticket_forms_db();
    $blueticket_db = blueticket_forms_db::get_instance();

    $blueticket_db->query('SELECT MAX(RegistrationNumber)+1 as RegNum FROM items');
    $myrow = $blueticket_db->row();

    if ($myrow['RegNum'] < 100000) {
        $myrow['RegNum'] = 100001;
    }

    $row_data->set('items.RegistrationNumber', $myrow['RegNum']);
}

function before_document_insert_callback($row_data, $xcrud) {
    $typeid = $row_data->get("TypeID");

    $blueticket_db = blueticket_forms_db::get_instance();

    $blueticket_db->query("SELECT * FROM document_types WHERE ID='$typeid'");
    $myrow = $blueticket_db->row();

    $invoicenumber = $myrow['Counting'];

    $blueticket_db->query("UPDATE document_types SET Counting = Counting + 1 WHERE ID='$typeid'");

    $row_data->set("InvoiceNumber", $invoicenumber);
}

function before_document_item_insert_callback($row_data, $xcrud) {
    $invoice = $row_data->get("InvoiceNumber");

    $blueticket_db = blueticket_forms_db::get_instance();

    $blueticket_db->query("SELECT * FROM invoices WHERE InvoiceNumber='$invoice'");
    $myrow = $blueticket_db->row();
    $type = $myrow['TypeID'];
    $customer = $myrow['CustomerID'];


    $blueticket_db->query("SELECT * FROM document_types WHERE ID='$type'");
    $row = $blueticket_db->row();

    $mark = $row['WHSign'];

    $blueticket_db->query("SELECT * FROM items WHERE RegistrationNumber = '" . $row_data->get("Barcode") . "'");
    $row = $blueticket_db->row();

    $old_price = $row['PurchasePrice'];
    $old_qty = $row['Qty'];

    $new_price = $row_data->get("Price");
    $new_qty = $row_data->get("Quantity") * $mark;

    $totalQty = $old_qty + $new_qty;

    if ($mark > 0) {
        if (($old_qty + $new_qty) != 0)
            $average_price = (($old_qty * $old_price) + ($new_qty * $new_price)) / ($old_qty + $new_qty);
        else
            $average_price = $new_price;

        $blueticket_db->query("UPDATE items SET PurchasePrice = $average_price WHERE RegistrationNumber = '" . $row_data->get("Barcode") . "'");
    }

    $blueticket_db->query("UPDATE items SET Qty = $totalQty WHERE RegistrationNumber = '" . $row_data->get("Barcode") . "'");

    $row_data->set("CartNr", $customer);
}

function before_document_item_delete_callback($primary, $xcrud) {
    $blueticket_db = blueticket_forms_db::get_instance();

    $blueticket_db->query('SELECT * FROM invoices_items WHERE ID = ' . $primary);
    $myrow = $blueticket_db->row();

    $regnum = $myrow['Barcode'];
    $qty = $myrow['Quantity'];
    $invoice = $myrow['InvoiceNumber'];

    $blueticket_db->query("SELECT * FROM invoices WHERE InvoiceNumber='$invoice'");
    $myrow = $blueticket_db->row();

    $type = $myrow['TypeID'];

    $blueticket_db->query("SELECT * FROM document_types WHERE ID='$type'");
    $myrow = $blueticket_db->row();

    $mark = $myrow['WHSign'];

    $qty = $qty * $mark;

    $blueticket_db->query("UPDATE items SET Qty = Qty - ($qty) WHERE RegistrationNumber = '$regnum'");
}
