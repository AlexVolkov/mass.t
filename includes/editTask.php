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


if($_POST['act'] == "load"){
		$query = mysql_query("SELECT * FROM `$gaSql[db]`.`$sTable` WHERE `id` = '$_POST[id]' LIMIT 1;"); 
		while($row = mysql_fetch_assoc($query)){
			$data[] = $row;
		}

		$query = mysql_query ("SELECT * FROM `$gaSql[db]`.`config`;"); 
		while($row = mysql_fetch_array($query)){ 
				$config[] = $row; 
			}

	if($data[0]['used_accounts'] == "random") { $rand = "checked"; } else {$ord = "checked";}
	if($data[0]['shortener'] == "googl") { $googl = "SELECTED"; } 
	if($data[0]['shortener'] == "bitly") { $bitly = "SELECTED"; } 

	$formData = "<form id=\"editTask\" method=\"post\">
			<ul>
			<li><label>Used accounts:</label><input style=\"width:50px;\" type=\"text\" name=\"used_accounts\" value=\"".$data[0]['used_accounts']."\"/></li>
			<input type=\"hidden\" name=\"id\" value=\"".$data[0]['id']."\"/>
			<li><label>Choose accounts </label><input type=\"radio\" name=\"ordering\" value=\"order\" ".$ord."/><small>in a sequence</small>
		<input type=\"radio\" name=\"ordering\" value=\"random\" ".$rand."/><small>randomly</small></li>";
			if($config[0]['opt_value'] == "on"){
			$formData .= "<li><label>Select shortener:</label><select name=\"shortener\">
				<option value=\"any\" >Any</option>
				<option value=\"googl\" ".$googl.">Goo.gl</option>
				<option value=\"bitly\" ".$bitly.">Bit.ly</option>
			</select></li>";
			}
			$formData .= "<li><label>Source:</label><textarea name=\"content\">".$data[0]['content']."</textarea></ul></form>

"; echo $formData;
	}


if($_POST['act'] == "save"){
	$data = $_POST['data']; 
	$id = $data[1][value];
		foreach($data as $num=>$string){
			$query = mysql_query("UPDATE `$gaSql[db]`.`$sTable` SET `$string[name]` = '$string[value]' WHERE `$sTable`.`id` = '$id';");
			//echo $query;
		}
ShowWindow("task updated", "success");
}

		
mysql_close($gaSql['link']);
	
?>
