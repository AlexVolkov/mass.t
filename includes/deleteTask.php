<?php
include_once './db.php';
include_once './messages.php';

	/* DB table to use */
	$sTable = "tasks";
	
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

		if($_POST['id'] !== '0'){
				$query = mysql_query("DELETE FROM `$gaSql[db]`.`$sTable` WHERE `$sTable`.`id` = $_POST[id]");
				if($query){
						echo ShowWindow("Task with id ".$_POST['id']." deleted", "success");
						} else {
						echo ShowWindow("Problems with database", "error");
						}
				} else {
					echo ShowWindow("Choose row to delete", "error");
					} 
		
		
					
	mysql_close($gaSql['link']);
	
?>
