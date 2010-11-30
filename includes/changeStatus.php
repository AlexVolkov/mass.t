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
		
		$id = $_POST['id'];
		$query = mysql_query("SELECT `status` FROM `$gaSql[db]`.`tasks` WHERE `tasks`.`id` = '$id'");
		$status = mysql_result($query, NULL);
		if(($status == 'paused') OR ($status == 'added')){
				$query = mysql_query("UPDATE `$gaSql[db]`.`tasks` SET `status` = 'started' WHERE `tasks`.`id` = '$id'") ;		
				ShowWindow("task started", "success"); 
				} else {
				$query = mysql_query("UPDATE `$gaSql[db]`.`tasks` SET `status` = 'paused' WHERE `tasks`.`id` = '$id'") ;		
				ShowWindow("task paused", "success"); 
				}
		mysql_close($gaSql['link']);
?>
