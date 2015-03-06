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
        $filter .= " (Name LIKE '%$item%' OR ID LIKE '%$item%')";
    else
        $filter .= " AND (Name LIKE '%$item%' OR ID LIKE '%$item%')";

    $i++;
}

$mydb->query("SELECT * FROM partners WHERE $filter ORDER BY Name LIMIT 20");

$i = 0;

$return = array();

foreach ($mydb->result() as $row) {
//    if ($i == 0)
//        $return .= '["' . $row['Name'] . '"';
//    else
//        $return .= ',"' . $row['Name'] . '"';
    array_push($return, array("Name" => $row["Name"], "ID" => $row['ID'], "Description" => $row['Name'] . '<br />' . $row['Address'] . '<br />' . $row['ZIP'] . ' ' . $row['City'] . '<br />' . $row['State'] . '<br />ICO: ' . $row['PID'] . '<br />DIC: ' . $row['VAT'] . '<br />IC-DPH: ' . $row['VATID'] . '<br />Kontakt:' . $row['Description']));
    $i++;
}

$return = json_encode($return);

print $return;
