<?php
require_once '../warehouse/object.class.php';
$bt = blueticket_forms::get_instance();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>blueticket&#8482;.forms</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <!-- Bootstrap core CSS -->
<!--        <link href="../lib/forms/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
         Bootstrap theme 
        <link href="../lib/forms/plugins/bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
        <script type="text/javascript" src="../lib/forms/plugins/jquery.min.js"></script>
        <script type="text/javascript" src="../lib/forms/plugins/bootstrap/js/bootstrap.min.js"></script>-->
        <!-- Glyphicons -->
        <link rel="stylesheet" href="../lib/forms/plugins/bootstrap/fonts/glyphicons/font-awesome/css/font-awesome.min.css">
        <style>
            body {
            }

            .logo
            {
                font-family: sans-serif;
                font-size: xx-large;
                font-weight: bold;
                letter-spacing: -2px;
            }

            .shadow
            {
                margin-top: 50px;
                box-shadow: 0 0 30px black;
                padding:0 15px 0 15px;
            }

            .user-menu
            {
                padding:0px;
                background-color: #f8f8f8;
                position:fixed;
                margin-top:50px;
                z-index: 9999;
                width:100%;
            }
            .user-block
            {
                float:left;
                display:block;
                width:100%;
                background-color: #a5987f;
                padding:10px;
            }
            .user-block-thumb
            {
                float:left;
                margin-right: 10px;
                width:50px;
                height:50px;
                border-radius: 4px;
                border:1px solid #e4e4e4;
            }
            .user-block-name
            {
                width:70%;
                float:left;
                font-size:12px;
                color:white;
                margin-top: 0px;
                margin-bottom: 0px;
            }
            .user-block-type
            {
                width:70%;
                float:left;
                font-size:12px;
                color:white;
                margin-bottom: 5px;
            }
            @media (max-width: 768px)
            {
                .search
                {
                    padding:15px 25px 10px 25px;
                }
                .user-menu-list li a
                {
                    padding:15px 25px 15px 25px;
                    width:100%;
                    display:block;
                    font-size:13px;
                    text-decoration: none;
                    color:#2e2e2e;
                }
                .user-block
                {
                    padding:15px 25px 15px 25px;
                }

                /*                .user-menu
                                {
                                    margin-top: 150px;
                                }*/
            }

            @media (min-width: 768px)
            {
                .search
                {
                    padding:15px 10px 15px 10px;
                }
                .user-menu-list li a
                {
                    padding:15px 15px 15px 15px;
                    width:100%;
                    display:block;
                    font-size:13px;
                    text-decoration: none;
                    color:#2e2e2e;
                }   
                .user-menu
                {
                    position: fixed;
                    width: 100%;
                    height: 100%;
                    background-color: #f8f8f8;
                    padding: 0px;
                }

            }

            .user-menu-list
            {
                padding:0px;
                margin:0px;
                list-style-type: none;
            }
            .user-menu-list li
            {
                width:100%;
                padding:0px;
                display:block;
                border-bottom: #e4e4e4 1px solid;
            }

            .user-menu-list li:hover
            {
                width:100%;
                display:block;
                border-bottom: #e4e4e4 1px solid;
            }

            .user-menu-list li a.create-profile-link
            {
                color:white;
                background-color: #d43f3a;
            }   

            .user-menu-list li a.create-profile-link:hover
            {
                color:white;
                background-color: #BD221C;
            }   
        </style>
    </head>

    <body role="document" style="background: url('../images/background.jpg') no-repeat center center; background-size: cover">

        <nav class="navbar navbar-inverse"> <!--  /*navbar-fixed-top*/ -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php"><span class="logo">blueticket&#8482;.forms</span></a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="#" id="menu1" data-toggle="dropdown">Home&nbsp;<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                            <li>
                                <a href="#" id="menu4" data-toggle="dropdown">Home</a>
                            </li>
                            <li>
                                <a href="#" id="menu5" data-toggle="dropdown">Home</a>
                            </li>
                            <li>
                                <a href="#" id="menu4" data-toggle="dropdown">Home</a>
                            </li>
                            <li>
                                <a href="#" id="menu5" data-toggle="dropdown">Home</a>
                            </li>
                            <li>
                                <a href="#" id="menu4" data-toggle="dropdown">Home</a>
                            </li>
                            <li>
                                <a href="#" id="menu5" data-toggle="dropdown">Home</a>
                            </li>
                        </ul>
                    </li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div><!--/.nav-collapse -->
        </nav>

        <div class="container-fluid content col-lg-12">
            <div class="col-md-12">
                <div class="panel panel-default transparent_white shadow">
                    <div class="panel-body">
                        <?php
//$bt = new blueticket_forms();

                        $bt->table('imports');
                        $bt->change_type('ImportFile', 'file', '', array('not_rename'=>true));
                        $bt->after_insert('after_import_insert_callback', 'blueticket.import.functions.php');
                        $bt->before_remove('delete_import_data', 'blueticket.import.functions.php');
                        $bt_detail = $bt->nested_table('import_items', 'ID', 'import_items', 'ImportID');
                        
                        echo $bt->render();
                        ?>
                    </div>
                </div>
            </div>
            <!--            <div class="row">
                            <div class="col-md-8">
                                <div class="panel panel-default transparent_white shadow">
                                    <div class="panel-body">
                                        <p class="logo">Why use blueticket.forms?</p>
                                        <dl>
                                            <dt>Extremely fast development</dt>
                                            <dd>You can create your own application without best knowledge of PHP. You need only to know, what you realy want.</dd>
                                            <dt>Reuseable code</dt>
                                            <dd>If you need some piece of code, you can use it as many times as you want</dd>
                                            <dt>Responsive design</dt>
                                            <dd>All of your applications, will be usable through spectrum of any devices, such as phones, tablets or PC's</dd>
                                            <dt>Fast engine</dt>
                                            <dd>blueticket.forms is designed to work fast with thousands of records per table</dd>
                                            <dt>Interconnection</dt>
                                            <dd>All of the applications objects wrote in the blueticket.forms can be interconnected together.</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="panel panel-default transparent_white shadow">
                                    <div class="panel-body">
                                         DOGFORSHOW Sign up form 
                                        <form id="frmSignIn" autocomplete="off">
                                            <div class="form-group">
                                                <span class="logo">Please enter database connection settings</span>
                                            </div>
                                            <div class="form-group">
                                                <input type="text" autocomplete="off" class="form-control" id="txtHost" placeholder="Database host">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" autocomplete="off" class="form-control" id="txtName" placeholder="Database name">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" autocomplete="off" class="form-control" id="txtUser" placeholder="Database username">
                                            </div>
                                            <div class="form-group">
                                                <input type="password" autocomplete="off" class="form-control" id="txtPassword" placeholder="Database password">
                                            </div>
                                        </form>
                                        <button type="submit" class="btn btn-danger btn-lg btn-block"><span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Save settings</button>
                                    </div>
                                </div>
                            </div>
                        </div>-->
        </div>
    </body>
</html>
