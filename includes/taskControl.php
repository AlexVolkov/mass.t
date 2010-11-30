<?php
include_once './db.php';
include_once './messages.php';
$link = mysql_connect($gaSql['server'], $gaSql['user'], $gaSql['password']);
mysql_select_db($gaSql['db']);
if (! $link) {
    ShowWindow('Could not connect: ' . mysql_error(), 'error');
    die();
}

if(isset($_POST['id']) && is_numeric($_POST['id']) ){
  if($_POST['act'] == "start"){
  exec("`php ./posting.php ". $_POST['id'] . " > /dev/null &`" );
  ShowWindow("Task started", "success");
  //echo ("php ./posting.php ". $_POST['id'] . " > /dev/null 2>&1");
  }
  if($_POST['act'] == "stop"){
      $query = mysql_query("UPDATE `tasks` SET `status` = 'stop' WHERE `tasks`.`id` = '".$_POST['id']."'");
    if (($query)) :
        ShowWindow("Task stopped", "success");
     else :
        ShowWindow("Can't stop the task", "error");
    endif;
  
  }

} else {
    ShowWindow("Task not defined", "error");
    die();
  }



?>