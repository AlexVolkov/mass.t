<?php
include_once("./lForm.php");
if(strlen($_COOKIE['UADDRVERIFY']) < 1){ 	
	login();
	}

include_once './db.php';


	/* DB table to use */
	$sTable = "proxy";
	
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

$query = mysql_query("SELECT * FROM proxy;");
	while($row = mysql_fetch_array($query)){
		echo($row[1]."\r\n");
	}
mysql_close($gaSql['link']);
	
?>
