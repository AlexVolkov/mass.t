<?php
include_once './db.php';
include_once './messages.php';

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

$rawPost = $_POST['arr'];


if(strlen($rawPost) > 1){
		preg_match_all("!pair_id=(.*?)(&|$)!si", $rawPost, $ids);
		preg_match_all("!val=(.*?)(&|$)!si", $rawPost, $idN);
				foreach($ids[1] as $num=>$id):
					if(strlen($id) > 1):
								$t = preg_replace("!a!", "", $idN[1][$num]);
								$id = urldecode($id);
								$query = mysql_query("UPDATE `$gaSql[db]`.`$sTable` SET `proxy` = '$id' WHERE `$sTable`.`id` = '$t';");
								if($query){ //echo $query;
										echo ShowWindow("Proxy with id ".$id." updated", "success");
										} else { //echo $query;
										echo ShowWindow("Problems with database", "error");
										}
						
						endif;
				endforeach;


}	else {
		echo ShowWindow("You need to choose what to save", "error");
		} 			
mysql_close($gaSql['link']);
	
?>

