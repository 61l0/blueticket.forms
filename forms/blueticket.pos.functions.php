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

    if($myrow['RegNum']<100000)
    {
        $myrow['RegNum'] = 100001;
    }
    
    $row_data->set('items.RegistrationNumber', $myrow['RegNum']);
}

function before_document_create_callback($row_data, $xcrud) {
    if(isset($_GET['type']))
        $type = $_GET['type'];
    else
        $type = 4;
    
    $row_data['invoices.PaymentTypeID']=$type;
}
