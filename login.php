<?php
session_start();

require_once 'object.class.php';

$bt = new blueticket_objects();

if (isset($_REQUEST['submit'])) {
    if ($_REQUEST['username'] == 'admin' && $_REQUEST['password'] == 'apr150') {
        $_SESSION['loggedin'] = 'admin';
        header('location: index.php');
    } else {
        echo '<span style="color:red; width:99%; text-align: center">' . $bt->getTranslatedText('Login incorrect - access denied') . '</span>';
    }
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="forms/themes/bootstrap/blueticket_forms.css">        
        <script src="forms/plugins/jquery.min.js"></script>
        <link href="forms/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet">
        <script src="forms/plugins/jquery-ui/jquery-ui.min.js"></script>

        <script type="text/javascript">
        </script>

        <style>
            @import "http://fonts.googleapis.com/css?family=Droid+Serif";
            /* Above line is used for online google font */
            body
            {
                font-family: sans-serif;
            }

            h2 {
                text-align:center;
                font-size:24px
            }
            hr {
                margin-bottom:30px
            }
            p {
                color:#000;
                font-size:16px;
                font-weight:700
            }
            #button {
                border:1px solid #0c799e;
                width:250px;
                padding:10px;
                font-size:16px;
                font-weight:700;
                color:#fff;
                border-radius:3px;
                background:linear-gradient(to bottom,#59d0f8 5%,#49c0e8 100%);
                cursor:pointer
            }
            #button:hover {
                background:linear-gradient(to bottom,#49c0e8 5%,#59d0f8 100%)
            }
            input[type=text] {
                margin-top:5px;
                margin-bottom:20px;
                width:96%;
                border-radius:5px;
                border:1px solid graytext;
                padding:5px 0
            }
            input[type=password] {
                margin-top:5px;
                margin-bottom:20px;
                width:96%;
                border-radius:5px;
                border:1px solid graytext;
                padding:5px 0
            }
            #name,#email {
                padding-left:10px
            }
            input[type=submit] {
                width:30%;
                border:1px solid #59b4d4;
                background:#0078a3;
                color:#eee;
                padding:3px 0;
                border-radius:5px;
                margin-left:33%;
                cursor:pointer
            }
            input[type=submit]:hover {
                border:1px solid #666;
                background:#555;
                color:#fff
            }
            .ui-dialog .ui-dialog-content {
                padding:2em
            }
            div.container {
                width:960px;
                height:610px;
                margin:50px auto;
                font-family:'Droid Serif',serif;
                position:relative
            }
            div.main {
                width:600px;
                margin-top:35px;
                float:left;
                padding:10px 55px 25px;
                background-color:rgba(204,204,191,0.51);
                border:15px solid #fff;
                box-shadow:0 0 10px;
                border-radius:2px;
                font-size:13px;
                text-align:center
            }

            .outer {
                display: table;
                position: absolute;
                height: 98%;
                width: 98%;
            }

            .middle {
                display: table-cell;
                vertical-align: middle;
            }

            .inner {
                background-color: lightblue;
                margin-left: auto;
                margin-right: auto;
                border-radius: 10px;
                padding: 15px 15px 15px 15px;
                width: 400px;
                height: 180px;
                border: 1px solid graytext;
            }
        </style>
    </head>
    <body>
        <form method="post">
            <div class="outer">
                <div class="middle">
                    <div class="inner">
                        <table style="width: 100%; height: 100%">
                            <tr style="height: 33%; vertical-align: middle">
                                <td>
<?php echo $bt->getTranslatedText("Username"); ?>
                                </td>
                                <td style="text-align: right">
                                    <input style="margin-top: 20px" type="text" name="username" id="username" />
                                </td>
                            </tr>
                            <tr style="height: 33%; vertical-align: middle">
                                <td>
<?php echo $bt->getTranslatedText("Password"); ?>
                                </td>
                                <td style="text-align: right">
                                    <input style="margin-top: 20px" type="password" name="password" id="password" />
                                </td>
                            </tr>
                            <tr style="height: 33%; vertical-align: middle">
                                <td colspan="2" style="text-align: right">
                                    <input style="height:30px" type="submit" name="submit" id="submit" text="<?php echo $bt->getTranslatedText("Login"); ?>"/>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </form>
        <script type="text/javascript">
            document.getElementById('username').focus();
        </script>
    </body>
</html>