<?php 
	error_reporting(0);
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$aColumns = array( 'id', 'source', 'used_accounts', 'ordering', 'progress', 'status' );
	//SELECT SQL_CALC_FOUND_ROWS `id`,`source`, `used_accounts`, `order`, `progress`, `content`, `status` FROM tasks
	
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "id";
	
	/* DB table to use */
	$sTable = "tasks";
	
	/* Database connection information */
	include_once './db.php';
	
	
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
	
	
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
				 	".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE (";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
	
	/* Individual column filtering */
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
		if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
		}
	}
	
	
	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere
		$sOrder
		$sLimit
	"; //echo $sQuery."\r\n";
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
	";
	$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultTotal = mysql_fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$sOutput = '{';
	//$sOutput .= '"sEcho": '.intval($_GET['sEcho']).', ';
	$sOutput .= '"sEcho": '.intval($_GET['sEcho']).', ';
	$sOutput .= '"iTotalRecords": '.$iTotal.', ';
	$sOutput .= '"iTotalDisplayRecords": '.$iFilteredTotal.', ';
	$sOutput .= '"aaData": [ ';
	while ( $aRow = mysql_fetch_array( $rResult ) )
	{
		$sOutput .= "[";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{


			if ( $aColumns[$i] == "version" )
			{
				/* Special output formatting for 'version' */
				$sOutput .= ($aRow[ $aColumns[$i] ]=="0") ?
					'"-",' :
					'"'.str_replace('"', '\"', $aRow[ $aColumns[$i] ]).'",';
			}
			else if ( $aColumns[$i] != ' ' )
			{
				/* General output */
				
				if($i == 4): $sOutput .= '"'.str_replace('"', '\"', '<div class=\'ui-progressbar ui-widget ui-widget-content ui-corner-all\'><div style=\'width: '.$aRow[ $aColumns[$i] ].'%;\' class=\'ui-progressbar-value ui-widget-header ui-corner-left\'></div><p class=\'text\'>'.$aRow[ $aColumns[$i] ]).'%</p></div>",';
					continue;
				endif;

				if($i == 5):
					if($aRow[ $aColumns[$i] ] == 'stop'){ 
						$buttin = str_replace('', '', '" <a title=\'Start task\' class=\'run\' href=\'#\' id=\'r'.$aRow[ $aColumns[0] ].'\'><img src=\'./images/icons/start.png\' /></a>'); 
						} else {
						$buttin = str_replace('', '', '" <a title=\'Stop task\' class=\'stop\' href=\'#\' id=\'s'.$aRow[ $aColumns[0] ].'\'><img src=\'./images/icons/pause.png\' /></a>'); }
				//continue;
				endif;
				$sOutput .= '"'.str_replace('"', '\"', $aRow[ $aColumns[$i] ]).'",';

			}
		}
		
		/*
		 * Optional Configuration:
		 * If you need to add any extra columns (add/edit/delete etc) to the table, that aren't in the
		 * database - you can do it here
		 */
		
				
		//$sOutput = substr_replace( $sOutput, "", -1 );
		//$sOutput .= '","';
		$sOutput .=  $buttin  . str_replace('', '', ' <a title=\'Edit task\' class=\'edit\' href=\'#\' id=\'e'.$aRow[ $aColumns[0] ].'\'><img src=\'./images/icons/edit.png\' /></a> <a title=\'Delete task\' class=\'delete\' href=\'#\' id=\''.$aRow[ $aColumns[0] ].'\'><img src=\'./images/icons/stop.png\' /></a> <a title=\'View log\' class=\'view_log\' href=\'./tmp/'.$aRow[ $aColumns[0] ].'.txt\' target=\'_blank\'><img src=\'./images/icons/log.png\' /></a>"');
		$sOutput .= "],";

	}
	$sOutput = substr_replace( $sOutput, "", -1 );
	$sOutput .= '] }';

	echo $sOutput;
?>
