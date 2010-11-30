<?php
include_once './db.php';
include_once './messages.php';

	/* DB table to use */
	$sTable = "config";
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
	 * no need to edit below this line
	 */
	
	/* 
	 * MySQL connection
	 */
	$gaSql['link'] =  mysql_pconnect( $gaSql['server'], $gaSql['user'], $gaSql['password']  ) or
		die( 'Could not open connection to server' );

	mysql_select_db( $gaSql['db'], $gaSql['link'] ) or 
		die( 'Could not select database '. $gaSql['db'] );


	$data = $_POST['send'];
mysql_query ("UPDATE `$gaSql[db]`.`$sTable` SET `opt_value` = 'off' WHERE 1 = 1;"); 
if($data){

				foreach($data as $num=>$id): 

								$query = mysql_query("UPDATE `$gaSql[db]`.`$sTable` SET `opt_value` = '$id[value]' WHERE `$sTable`.`opt_name` = '$id[name]';"); 
								if($query){ 
										
										} else { //echo $query;
										echo ShowWindow("Problems with database", "error");
										continue;
										}

							

				endforeach;
$mess .= "Settings updated";
echo ShowWindow($mess, "success");

}	else {
		echo ShowWindow("nothing to save", "error");
		} 	
		
mysql_close($gaSql['link']);
	
?>

