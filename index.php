<?php
error_reporting(E_ALL);
require_once ('object.class.php');
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" type="text/css" href="forms/themes/bootstrap/blueticket_forms.css">        
    </head>
    <body>
        <?php
        $bt = new blueticket_objects();


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
                        break;
                }
            } else {
                ?>

                <?php
            }
            ?>
        </div>
    </body>
</html>