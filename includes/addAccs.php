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
	$gaSql['link'] =  mysql_pconnect( $gaSql['server'], $gaSql['user'], $gaSql['password']  ) or
		die( 'Could not open connection to server' );

	mysql_select_db( $gaSql['db'], $gaSql['link'] ) or 
		die( 'Could not select database '. $gaSql['db'] );


	$data = $_POST['data'];
if($data){
	$data = explode("\n", $data);
				foreach($data as $num=>$id):
					if(strlen($id) > 1):
								if(!strpos($id, ":")){ 
													//$mess .= "line " . $num . " isn't contains pair like login<strong>:</strong><br />"; 
													continue;
												}
								$id = trim($id);
								$query = mysql_query("INSERT INTO `$gaSql[db]`.`$sTable` (
																			`id` ,
																			`pair` ,
																			`error`
																			)
																				VALUES (
																			'' , 
																			'$id', 
																			''
																			);

													
													");
								if($query){ //echo $query;
										$mess .= " ".$id." inserted<br />";
										} else { //echo $query;
										echo ShowWindow("Problems with database", "error");
										continue;
										}
						else:
							$mess .= "<p style=\"color:red\">empty line at ".$num." string</p>";
							
						endif;
				endforeach;
mysql_query("CREATE TEMPORARY TABLE `temp`(id VARCHAR(10), pair VARCHAR(255), error VARCHAR(255))TYPE=HEAP;
INSERT INTO `temp`(`pair`) SELECT DISTINCT `pair` FROM `accounts`;
DELETE FROM `accounts`;
INSERT INTO `accounts`(`pair`) SELECT (`pair`) FROM `temp`;");
echo ShowWindow("Accounts added", "success");

}	else {
		echo ShowWindow("You need to choose what to add", "error");
		} 	
		
mysql_close($gaSql['link']);
	
?>

