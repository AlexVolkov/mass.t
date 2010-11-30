<?php
include_once './db.php';
include_once './messages.php';
/* DB table to use */
$sTable = "accounts";
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
	 * no need to edit below this line
	 */
/* 
	 * MySQL connection
	 */
$gaSql['link'] = mysql_pconnect($gaSql['server'], $gaSql['user'], $gaSql['password']) or die('Could not open connection to server');
mysql_select_db($gaSql['db'], $gaSql['link']) or die('Could not select database ' . $gaSql['db']);

if ($_POST['id'] == '-1') {
    $query = mysql_query("DELETE FROM `$gaSql[db]`.`$sTable` WHERE 1");
    if ($query) {
        echo ShowWindow("All list deleted", "success");
    } else {
        echo ShowWindow("Problems with database", "error");
    }
    break;
}
if ($_POST['id'] !== '') {
    $ids = explode("|", $_POST['id']);
    if ($ids) {
        foreach ($ids as $id) :
            if (strlen($id) > 0) :
                $id = preg_replace("!|!si", "", $id);
                $id = preg_replace("!d!si", "", $id);
                $query = mysql_query("DELETE FROM `$gaSql[db]`.`$sTable` WHERE `$sTable`.`id` = $id");
                if ($query) {
                    echo ShowWindow("Accounts deleted", "success");
                } else {
                    echo ShowWindow("Problems with database", "error");
                }
            
						
						endif;
        endforeach
        ;
    } else {
        echo no;
    }
} else {
    echo ShowWindow("Choose something to delete", "error");
}
mysql_close($gaSql['link']);
?>
