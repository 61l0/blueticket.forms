<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function import_csv($par_file, $par_ImportID) {
    $row = 1;

    $fieldsdef = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
        'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
        'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
        'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ');

    $blueticket_db = blueticket_forms_db::get_instance();
    
    if (($handle = fopen("$par_file", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $num = count($data);
            $row++;
            $fields = "(ImportID";
            $values = "VALUES($par_ImportID";
            for ($c = 0; $c < $num; $c++) {
                $fields .= ",";
                $values .= ",";
                $fields .= "`" . $fieldsdef[$c] . "`";
                $values .= "'" . addslashes($data[$c]) . "'";
            }
            $fields .= ")";
            $values .= ")";

            $query = "INSERT INTO import_items $fields $values";
            $blueticket_db->query($query);
        }

        fclose($handle);
    }
}

function delete_import_data($primary, $blueticket_forms){
    $db = blueticket_forms_db::get_instance();
    $db->query('DELETE FROM import_items WHERE ImportID = ' . $db->escape($primary));
}

function after_import_insert_callback($row_data, $primary) {
    $blueticket_db = blueticket_forms_db::get_instance();

    $blueticket_db->query("SELECT * FROM imports WHERE ID=$primary");
    $myrow = $blueticket_db->row();

    $zip = new ZipArchive();

    $filename = getcwd() . '/../uploads/' . $myrow['ImportFile'];
    $zip->open($filename);

    $directory = getcwd() . '/../../importer/current_import/';

    foreach (glob("{$directory}/*") as $file) {
        unlink($file);
    }

    $zip->extractTo($directory);

    foreach (glob("{$directory}/*") as $file) {
        if (strpos($file, '.csv') > 0)
            import_csv($file, $primary);
    }
}
