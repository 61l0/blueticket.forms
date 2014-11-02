<?php
error_reporting(0);
require_once ('object.class.php');

$bt = new blueticket_objects();

if (isset($_GET['report']) && $_GET['report'] == 'print') {
    
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="forms/themes/bootstrap/blueticket_forms.css">        

        <script type="text/javascript">
            function item_select(par_regnum, par_name)
            {
                $.ajax({
                    url: "add_print_item.php?reg=" + par_regnum + "&name=" + par_name,
                }).done(function () {
                    alert("Ok:\n" + par_regnum + " - " + par_name);
                });
            }
        </script>

    </head>
    <body>
        <?php
        echo $bt->generateMenu();
        ?>
        <div style="overflow: auto; position: absolute; top:50px; left: 10px; right: 10px; bottom: 10px; border: 1px solid graytext">
            <?php
            if (isset($_GET['report'])) {
                switch ($_GET['report']) {
                    case 'cards':
                        echo $bt->generateItems();
                        break;
                    case 'stats':
                        echo $bt->generateInvoices();
                        break;
                    case 'trans':
                        echo $bt->generateTranslate();
                        break;
                    case 'print':
                        echo $bt->printItems();
                        break;
                }
            } else {
                echo $bt->generateItems();
            }
            ?>
        </div>
    </body>
</html>