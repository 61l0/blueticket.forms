<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

use PFBC\Form;
use PFBC\Element;

include("pfbc/PFBC/Form.php");
include("forms/blueticket_forms.php");

class blueticket_app {

    public $app_data;
    public $db;

    public function __construct($par_AppName) {
        $app_query = $this->getQuery("SELECT codes.*, codes_meta.meta_value FROM codes INNER JOIN codes_meta ON codes_meta.code_id = codes.code_id WHERE (codes.code_type = 'php_code' OR codes.code_type = 'php_main') AND codes_meta.meta_key = 'code' ORDER BY codes.code_type ASC, codes.code_name ASC");

        $this->app_data = $app_query;
    }

    public function __destruct() {
        mysql_free_result($this->app_data);
    }

    public function execute() {
        while ($app_row = mysql_fetch_object($this->app_data)) {
            eval($app_row->meta_value);
        }
    }

    function dbConnect() {
        $dbHostname = blueticket_forms_config::$dbhost;
        $dbDatabase = blueticket_forms_config::$dbname;
        $dbUsername = blueticket_forms_config::$dbuser;
        $dbPassword = blueticket_forms_config::$dbpass;
        $Connect = mysql_connect($dbHostname, $dbUsername, $dbPassword) or die('<storng>Database connect error: </strong>' . mysql_error());
        mysql_query("SET NAMES '" . blueticket_forms_config::$dbencoding . "'", $Connect);
        return array($dbDatabase, $Connect);
    }

    function getQuery($myQuery, $typeQuery = "GET") {
        list($dbDatabase, $Connect) = $this->dbConnect();
        mysql_select_db($dbDatabase, $Connect);
        if ($typeQuery != "GET") {
            mysql_real_escape_string($myQuery);
        }
        $processQuery = mysql_query($myQuery, $Connect);
        return $processQuery;
    }

}
