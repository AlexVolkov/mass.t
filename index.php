<?php 
if(!file_exists('./includes/db.php')) {
    header('Location:./install/install.php');
}
session_start();

include_once("./includes/lForm.php");
//echo date('l jS \of F Y h:i:s A');
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
        setcookie("UADDRVERIFY", $checkSum, time()+36000);
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

//load config

    include_once './includes/db.php';
    $gaSql['link'] =  mysql_pconnect( $gaSql['server'], $gaSql['user'], $gaSql['password']  ) or
            die( 'Could not open connection to server' );

    mysql_select_db( $gaSql['db'], $gaSql['link'] ) or
            die( 'Could not select database '. $gaSql['db'] );

    $query = mysql_query ("SELECT * FROM `$gaSql[db]`.`config`;");
    while($row = mysql_fetch_array($query)) {
        $config[] = $row;
    }
    //total accounts and proxies and tasks
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


    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Twindexator</title>
        <meta name="generator"
              content="HTML Tidy for Linux/x86 (vers 25 March 2009), see www.w3.org" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="./css/default.css" type="text/css"
              media="screen" />
        <link rel="stylesheet"
              href="./css/smoothness/jquery-ui-1.8.4.custom.css" type="text/css"
              media="screen" />
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
            var oTable;
            var iSelected = String();

            function delcookie()
            {
                var tmp_date=new Date()-10;
                document.cookie="UADDRVERIFY=;expires=Thu, 01-Jan-70 00:00:01 GMT;";
                document.cookie="PHPSESSID=;expires=Thu, 01-Jan-70 00:00:01 GMT;";
                window.location.href="./?action=exit";

            }



            $(function() {
                $("#tabs1").tabs();

                $.fn.clearForm = function() {

                    return this.each(function() {

                        var type = this.type, tag = this.tagName.toLowerCase();

                        if (tag == 'form')

                        return $(':input',this).clearForm();

                    if (type == 'text' || type == 'password' || tag == 'textarea')

                    this.value = '';

                else if (tag == 'select')

                this.selectedIndex = 0;


        });

    };


    $( "#addTaskDialog" ).dialog({
        autoOpen: false,
        resizable: false,
        modal: true,
        width: 560,
        buttons: {
            "Add task": function() {
                $( this ).dialog( "close" );
                $.post("./includes/submitTask.php", $("form").serialize(),
                function(data){
                    $("#showMessage").html(data);
                    //oTable.fnDraw();
                    location.reload();
                    $("#showMessage").css({display: "block"});
                    setTimeout(function(){
                        $('#showMessage').fadeOut('slow', function() {
                            // Animation complete
                        });

                    },3000);
                } );
                $("form").clearForm();
                $("#accs").val('0');


            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }

    });



    $( "#dialog-confirm" ).dialog({
        resizable: false,
        height:200,
        width:360,
        modal: true,
        autoOpen: false,
        buttons: {
            "Delete task": function() {
                var ID = $('#DeleteId').attr('value');
                $( this ).dialog( "close" );
                $.post("./includes/deleteTask.php", { id: ID},
                function(data){
                    $("#showMessage").html(data);
                    //oTable.fnDraw();
                    location.reload();
                    $("#showMessage").css({display: "block"});
                    setTimeout(function(){
                        $('#showMessage').fadeOut('slow', function() {
                            // Animation complete
                        });

                    },3000);

                });

            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });

    $( "#dialog-edit" ).dialog({
        resizable: false,
        height:550,
        width:630,
        modal: true,
        autoOpen: false,
        buttons: {
            "Edit task": function() {
                $( this ).dialog( "close" );
                var sData = $("#editTask").serializeArray();
                $.post("./includes/editTask.php", { data: sData, act: "save"},
                function(data){
                    $("#showMessage").html(data);
                    oTable.fnDraw();
                    $("#showMessage").css({display: "block"});
                    setTimeout(function(){
                        $('#showMessage').fadeOut('slow', function() {
                        });

                    },3000);
                });

            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });

    $( "#addTask" ).click(function() {
        $( "#addTaskDialog" ).dialog( "open" );
        return false;
    });

    $( "#DeleteTask" ).click(function() {
        $( "#dialog-confirm" ).dialog( "open" );
        return false;
    });
    $( "#example img" ).click(function() {
        alert( "open" );
        return false;
    });




    $(document).ready(function() {
        oTable = $('#example').dataTable( {
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "./includes/fetchTasksJSON.php",
            "bJQueryUI": true,
            "bFilter": false,
            "aoColumns": [
                { "sWidth": "30px" },
                { "sWidth": "80px" },
                { "sWidth": "70px" },
                { "sWidth": "60px" },
                { "sWidth": "250px" },
                { "sWidth": "60px", "bVisible":    false  },
                { "sWidth": "60px" }
            ],
            "fnDrawCallback": function() {

            }


        } );

        $('#example tbody tr ').live('click', function () {
            var aData = oTable.fnGetData( this );
            $('#example tbody tr').removeClass('row_selected');
            $(this).toggleClass('row_selected');
            $('#DeleteId').attr('value', aData[0]);
        } );

        $('.pause').live('click',function () {
            var pid = $(this).attr('id');
            $.post("./includes/changeStatus.php", { id: pid},
            function(data){
                $("#showMessage").html(data);
                oTable.fnDraw();
                $("#showMessage").css({display: "block"});
                setTimeout(function(){
                    $('#showMessage').fadeOut('slow', function() {
                    });

                },3000);

            });

        }
    );
        $('.delete').live('click',function () {
            $('#DeleteId').attr('value', $(this).attr('id'));
            $( "#dialog-confirm" ).dialog( "open" );
            return false;
        });
        $('.edit').live('click',function () {
            var eId = $(this).attr('id').replace("e","");
            $('#editId').attr('value', eId);
            $.post("./includes/editTask.php", {id:eId, act: "load"},
            function(data){
                $('#dialog-edit').html(data);
            });
            $( "#dialog-edit" ).dialog( "open" );
            return false;
        });
        $('.run').live('click',function () {
            var rId = $(this).attr('id').replace("r","");
            $.post("./includes/taskControl.php", {id:rId, act: "start"},
            function(data){
                $("#showMessage").html(data);
                oTable.fnDraw();
                $("#showMessage").css({display: "block"});
                setTimeout(function(){
                    $('#showMessage').fadeOut('slow', function() {
                    });

                },3000);
            });
            return false;

        });
        $('.stop').live('click',function () {
            var rId = $(this).attr('id').replace("s","");
            $.post("./includes/taskControl.php", {id:rId, act: "stop"},
            function(data){
                $("#showMessage").html(data);
                oTable.fnDraw();
                $("#showMessage").css({display: "block"});
                setTimeout(function(){
                    $('#showMessage').fadeOut('slow', function() {
                    });

                },3000);
            });
            return false;
        });

    } );




});

var refreshId = setInterval(function()
{
    oTable.fnDraw();
}, <?php echo ($config[2]['opt_value'] * 1000);?>);


        </script>
    </head>
    <body>

        <div id="showMessage" style="display: none :"></div>


        <div id="dialog-confirm" title="Delete this task?">
            <p><span class="ui-icon ui-icon-alert"
                     style="float: left; margin: 0 7px 20px 0;"></span> This task will be
                permanently deleted and cannot be recovered. Are you sure?</p>
            <form method="get">
                <input type="hidden" id="DeleteId" value="0" />
            </form>
        </div>

        <!--edit window-->
        <div id="dialog-edit" title="Task editing">
            <div id="InnerEdit"></div>
        </div>



        <!--add task window-->

        <div id="addTaskDialog" title="Add new task">
            <form id="addTask">




                <div id="tabs1">
                    <ul id="navMenu">
                        <li><a href="#tabs-1">Add list of tweets</a></li>
                        <li><a href="#tabs-2">Paste feeds</a></li>
                    </ul>
                    <div id="tabs-1"><label>Paste tweets:</label><textarea name="tweets"></textarea><br /><small>separate with colon</small></div>
                    <div id="tabs-2"><label>Paste feeds:</label><textarea name="feeds"></textarea><br /><small>separate with colon</small></div>
                </div>




                <div class="buttons"><br />
                    <label>Use num. of accounts:</label> <input type="text" size="5"	value="0" name="numaccs" id="accs" /><small>(0 - use all,max - <?php echo $counts[0]['accs']; ?>)</small><br />
                    <br />
                    <label><input type="radio" name="radio" value="order" checked="checked" /> Choose in sequence</label>
                    <label><input type="radio" name="radio" value="random" /> Choose randomly</label>
                        <?php
    if($config[0]['opt_value'] == "on") {
        echo "<br /><label>Select shortener:</label><select name=\"shortener\">
				<option value=\"any\" >Any</option>
				<option value=\"googl\" >Goo.gl</option>
				<option value=\"bitly\" >Bit.ly</option>
			</select>";
    }
    ?>
                </div>

            </form>
        </div>

        <!--add task window-->



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
                <li class="selected"><a href='./index.php'>Tasks</a></li>
                <li><a href='./accounts.php'>Accounts</a></li>
                <li><a href='./proxy.php'>Proxy</a></li>
                <li><a href='./setts.php'>Settings</a></li>
            </ul>
        </div>

        <div id="cont">
            <div id="wrap">
                <div class="boxed">
                    <p class="caption">Active tasks</p>
                    <span id="icons">
                        <a href="#addTaskDialog" id="addTask"><img src='./images/icons/add.png' alt="Add task" title="Add task"/></a>
                        <a href="#DeleteTaskDialog" id="DeleteTask"><img src='./images/icons/delete.png' alt="Delete task" title="Delete task" /></a>
                        <span class="info tips" style="width:780px;">
    <?php $hints = array(
            "If you're want to use all accounts - type in 0 in account's field",
            "Proxies and accounts have been checked while task processing",
    );
    $rand = array_rand($hints, 1);
    echo $hints[$rand];
    ?></span>
                    </span>

                </div>
                <div id="dt_example">
                    <table cellpadding="0" cellspacing="0" border="0" class="display" id="example">
                        <thead>

                            <tr>
                                <th>ID</th>
                                <th>TYPE</th>
                                <th>USED ACCS</th>
                                <th>METHOD</th>
                                <th>%</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="dataTables_empty">Loading data from
			server</td>
                            </tr>
                        </tbody>
                        <tfoot>

                            <tr>
                                <th>ID</th>
                                <th>TYPE</th>
                                <th>USED ACCS</th>
                                <th>METHOD</th>
                                <th>%</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
            <!--wrap ends --> <!--<div class="lowershadow">&nbsp;</div>--></div>

    </body>
</html>
    <?php
}
session_destroy();
?>

