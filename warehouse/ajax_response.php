<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once './forms/blueticket_forms.php';

//$mydb = new blueticket_forms_db();
$mydb = blueticket_forms_db::get_instance();

$name = "";
$name = $_REQUEST['name'];

$name = explode(" ", $name);

$filter = "";

$i = 0;

foreach ($name as $item) {
    if ($i == 0)
        $filter .= " (Name LIKE '%$item%' OR RegistrationNumber LIKE '%$item%')";
    else
        $filter .= " AND (Name LIKE '%$item%' OR RegistrationNumber LIKE '%$item%')";

    $i++;
}

$mydb->query("SELECT * FROM items WHERE $filter ORDER BY Name LIMIT 20");

$i = 0;

$return = array();

foreach ($mydb->result() as $row) {
//    if ($i == 0)
//        $return .= '["' . $row['Name'] . '"';
//    else
//        $return .= ',"' . $row['Name'] . '"';

    $taxid = $row['TaxID'];
    $unitid = $row['UnitID'];

    $mydb->query("SELECT * FROM units WHERE ID='$unitid'");
    $unitrow = $mydb->row();
    $unit = $unitrow['Name'];
    $mydb->query("SELECT * FROM taxes WHERE ID='$taxid'");
    $taxrow = $mydb->row();
    $tax = $taxrow['Value'];

    array_push($return, array("Tax" => $tax, "Unit" => $unit, "Description" => $row["RegistrationNumber"] . '|' . $row['Name'] . ' [' . number_format($row['Qty'], 2) . ']', "Name" => $row['Name'], "RegNum" => $row['RegistrationNumber'], "Price" => $row['Price']));
    $i++;
}

$return = json_encode($return);

print $return;
