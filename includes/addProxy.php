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


	$data = $_POST['data'];
if($data){
	$data = explode("\n", $data);
				foreach($data as $num=>$id):
					if(strlen($id) > 1):
								if(!strpos($id, ":")){ 
													//$mess .= "line " . $num . " isn't contains pair like ip<strong>:</strong>port<br />"; 
													continue;
												}
								$id = trim($id);
								$query = mysql_query("INSERT INTO `$gaSql[db]`.`$sTable` (
																			`id` ,
																			`proxy` ,
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

mysql_query("CREATE TEMPORARY TABLE `temp`(id VARCHAR(10), proxy VARCHAR(255), error VARCHAR(255))TYPE=HEAP;
INSERT INTO `temp`(`proxy`) SELECT DISTINCT `proxy` FROM `proxy`;
DELETE FROM `proxy`;
INSERT INTO `proxy`(`proxy`) SELECT (`proxy`) FROM `temp`;");
echo ShowWindow("Proxy added", "success");

}	else {
		echo ShowWindow("You need write something to add", "error");
		} 	
		
mysql_close($gaSql['link']);
	
?>

