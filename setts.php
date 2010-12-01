<?php 
session_start();
include_once("./includes/lForm.php");

if($_POST) {
    $mess = "test";
    $key = $_POST['key'];
    $sAnsw = file_get_contents("http://holms-info.ru/check.php?key=".$key);
    if(!$sAnsw) {
        $sAnsw1 = file_get_contents("http://interglob.ru/check.php?key=".$key);

        if(!$sAnsw1) {
            login("Can't verify your license. ");
            die();
        } else {
            $sAnsw = $sAnsw1;
        }
    }
    if($sAnsw == "ERR:LICENSE_DOESNT_EXIST") {
        login("License doesn't exists.");
        die();
    }
    if($sAnsw == "ERR:SERVER_IP_MISMATCH") {
        login("You cannot use this software from this ip.");
        die();
    }
    $checkSum = "OK:". md5( substr($key, 0, 5) . substr($_SERVER['SERVER_ADDR'], 0, 5) );
    //$checkSum = "OK:". (substr($key, 0, 5) . substr('95.83.162.20', 0, 5) . substr(date("U"), -4, 3));
    if($checkSum == $sAnsw) {
        $checkSum =  md5(substr($_SESSION['[PHPSESSID'], 0, 5) . substr($_SERVER['SERVER_ADDR'], 0, 5) );
        setcookie("UADDRVERIFY", $checkSum, time()+3600);
        header("Location:./index.php");
    } else {
        login("Can't set cookies");
        die();
    }
}



if(strlen($_COOKIE['UADDRVERIFY']) < 1) {
    login(NULL);
}

else {
    $checkSum =  md5(substr($_SESSION['[PHPSESSID'], 0, 5) . substr($_SERVER['SERVER_ADDR'], 0, 5) );
    if($checkSum != $_COOKIE['UADDRVERIFY']) {
        login("Cookie not valid for this session");
        die();
    }



    include_once './includes/db.php';
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

    $query = mysql_query ("SELECT * FROM `$gaSql[db]`.`$sTable`;");
    while($row = mysql_fetch_assoc($query)) {
        $data[] = $row;
    }
    $query = mysql_query("SELECT  (
						   SELECT COUNT(*)
						   FROM   tasks
						   ) AS task,
						   (
						   SELECT COUNT(*)
						   FROM   proxy
						   ) AS proxy,
						   (
						   SELECT COUNT(*)
						   FROM   accounts
						   ) AS accs
						FROM    dual
		");
    while($row = mysql_fetch_assoc($query)) {
        $counts[] = $row;
    }
    mysql_close($gaSql['link']);
    ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Twindexator</title>
        <meta name="generator" content="HTML Tidy for Linux/x86 (vers 25 March 2009), see www.w3.org" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="./css/default.css" type="text/css"	media="screen" />
        <link rel="stylesheet" href="./css/smoothness/jquery-ui-1.8.4.custom.css" type="text/css"	media="screen" />
        <script type="text/javascript" src="./js/jquery.min.js" charset="utf-8"></script>
        <script type="text/javascript" src="./js/jquery-ui.min.js"
        charset="utf-8"></script>
        <script type="text/javascript" src="./js/jquery.uniform.min.js"
        charset="utf-8"></script>
        <script type="text/javascript" src="./js/jquery.ui.core.js"></script>
        <script type="text/javascript" src="./js/jquery.ui.widget.js"></script>
        <script type="text/javascript" src="./js/jquery.ui.tabs.js"></script>
        <script type="text/javascript" src="./js/jquery.dataTables.js"></script>

        <script type="text/javascript" charset="utf-8">
            function delcookie()
            {
                var tmp_date=new Date()-10;
                document.cookie="UADDRVERIFY=;expires=Thu, 01-Jan-70 00:00:01 GMT;";
                document.cookie="PHPSESSID=;expires=Thu, 01-Jan-70 00:00:01 GMT;";
                window.location.href="./?action=exit";

            }
            $(function() {

                $("#setts").click(function() {
                    var sData = $("form:first").serializeArray();
                    $.post("./includes/settings.php", {send: sData},
                    function(data){
                        $("#showMessage").html(data);
                        $("#showMessage").css({display: "block"});
                        setTimeout(function(){
                            $('#showMessage').fadeOut('slow', function() {
                                // Animation complete
                                location.reload();
                            });

                        },3000);

                    });
                });


            });


        </script>


    </head>
    <body>

        <div id="showMessage" style="display: none;"></div>
        <div class="shadow"></div>

        <img src='./images/logo.png' style="display:block;margin:15px auto;text-align:center;width:320px;" />

        <br />
        <div id="leftcont">
            <span style="display:block; width:210px; background:#E4F5FD; padding:10px;font-size:80%">
                <strong>Info</strong><br />
				accounts: <?php echo $counts[0]["accs"];?><br />
				proxies: <?php echo $counts[0]["proxy"];?><br />
				tasks: <?php echo $counts[0]["task"];?><br />
                <a style="color:#333" href="javascript:delcookie();">logout</a>
            </span>
            <ul class='menu1'>
                <li><a href='./index.php'>Tasks</a></li>
                <li><a href='./accounts.php'>Accounts</a></li>
                <li><a href='./proxy.php'>Proxy</a></li>
                <li class="selected"><a href='./setts.php'>Settings</a></li>
            </ul>
        </div>

        <div id="cont">
            <div id="wrap">
                <div class="boxed">
                    <p class="caption">Settings</p>

                </div>
                <div id="dt_example">

                    <form method="post">
                        <ul>
                            <li><label>Use accounts with errors?</label><input type="checkbox" name="use_accs_with_errors" <?php if($data[5]['opt_value'] == "on") {
        echo 'checked';
    }?>/> <small>Accounts with any last errors(can't login, suspended, etc) will be used</small></li>
                            <li><label>Use proxy?</label><input type="checkbox" name="use_proxy" <?php if($data[1]['opt_value'] == "on") {
        echo 'checked';
    }?>/><small>connect twitter through proxy</small></li>
                            <li><label>Use proxy with errors?</label><input type="checkbox" name="use_proxy_with_errors" <?php if($data[4]['opt_value'] == "on") {
        echo 'checked';
    }?>/><small>all proxy in databse will be used, errors will be ignored</small></li>
                            <li><label>Use shortener?</label><input type="checkbox" name="use_shortener" <?php if($data[0]['opt_value'] == "on") {
        echo 'checked';
    }?>/><small>All external links will be shorted by shortening services</small></li>
                            <!--<li><label>Launch task after adding?</label><input type="checkbox" name="launch_after_add" <?php if($data[3]['opt_value'] == "on") {
        echo 'checked';
    }?>/></li>-->
                            <li><label>Task table refresh interval:</label><input type="text" name="refresh_task_table_intval" value="<?php echo $data[2]['opt_value'];?>"/> <small>sec. Refresh interval to table renew on tasks page</small></li>
                            <li><input type="button" value="Save configuration" style="width:150px;" id="setts"/></li>
                        </ul>
                    </form>



                </div>

            </div>
            <!--wrap ends --> <!--<div class="lowershadow">&nbsp;</div>--></div>

    </body>
</html>
    <?php
}
session_destroy();
?>
