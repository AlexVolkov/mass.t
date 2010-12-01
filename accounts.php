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
//print_r($counts);

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

                $( "#dialog-confirm" ).dialog({
                    resizable: false,
                    height:200,
                    width:360,
                    modal: true,
                    autoOpen: false,
                    buttons: {
                        "Delete account": function() {
                            var an = $("#fnSel").attr('value');
                            $( this ).dialog( "close" );
                            $.post("./includes/deleteAccs.php", { id: an},
                            function(data){
                                $("#showMessage").html(data);
                                //oTable.fnDraw();
                                location.reload();
                                $("#showMessage").css({display: "block"});
                                setTimeout(function(){
                                    $('#showMessage').fadeOut('slow', function() {
                                        // Animation complete
                                    });

                                },2000);

                            });

                        },
                        Cancel: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });
                $( "#add-accs-dialog" ).dialog({
                    resizable: false,
                    height:610,
                    width:560,
                    modal: true,
                    autoOpen: false,
                    buttons: {
                        "Add accounts": function() {
                            var text = $('textarea#accList').val();
                            $( this ).dialog( "close" );
                            $.post("./includes/addAccs.php", { data: text},
                            function(data){
                                $("#showMessage").html(data);
                                //oTable.fnDraw();
                                location.reload();
                                $("#showMessage").css({display: "block"});
                                setTimeout(function(){
                                    $('#showMessage').fadeOut('slow', function() {
                                        // Animation complete
                                    });

                                },15000);

                            });

                        },
                        Cancel: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });

                //delete by the button from this row
                $( "#delete" ).click(function() {
                    $( "#dialog-confirm" ).dialog( "open" );
                    return false;
                });
                $( "#addAccs" ).click(function() {
                    $( "#add-accs-dialog" ).dialog( "open" );
                    return false;
                });
                $("#clearAll").click(function() {
                    $("#fnSel").attr('value', '-1');
                    $( "#dialog-confirm" ).dialog( "open" );
                    return false;
                });
                //edit line

                $('#saveAll').click( function() {
                    var sData = $('input', oTable.fnGetNodes()).serialize();
                    $.post("./includes/editAccs.php", {arr: sData},
                    function(data){
                        $("#showMessage").html(data);
                        oTable.fnDraw();
                        $("#showMessage").css({display: "block"});
                        setTimeout(function(){
                            $('#showMessage').fadeOut('slow', function() {
                                // Animation complete
                            });

                        },2000)
                    });
                } );



                $(document).ready(function() {

                    oTable = $('#example').dataTable( {
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": "./includes/fetchAccountsJSON.php",
                        "aLengthMenu": [[50, 100, 500, -1], [50, 100, 500, 'All']],
                        "iDisplayLength": 50,
                        "sDom": '<l<"toolbar">f>rtpi',

                        "aoColumns": [
                            { "sWidth": "10px" },
                            { "sWidth": "280px" },
                            { "sWidth": "280px" },
                            { "sWidth": "20px" },
                        ],
                        "fnDrawCallback": function() {

                        }


                    } );

                    $('#example tbody tr ').live('click', function () {
                        var aData = oTable.fnGetData( this );
                        var an = $("#fnSel").attr('value');
                        if ( $(this).hasClass('row_selected') ){
                            $(this).removeClass('row_selected');
                            $("#fnSel").attr('value', an.replace("|" + aData[0], ""));
                        }

                        else	{
                            $(this).addClass('row_selected');
                            $("#fnSel").attr('value', an + "|" + aData[0]);
                        }

                    } );
                    $('.edit').live('click',function () {
                        var an = $(this).attr('id');
                        var cId = an.replace('e','a');
                        var cVal = $("span#" + cId).text();
                        var check = $("span#" + cId).attr('class');
                        if(check == "editable"){
                            var form = "<form class='editform' action='#'><input type='hidden' name='val' value='" + cId + "' \/><input id='test' type='text' name='pair_id' value='" + cVal + "' /\> </form>";
                            $("span#" + cId).attr('class', 'editing');
                            $('#' + cId).html( form );
                        } else {
                            var cVal = $("#" + cId + " form input").val();
                            $('#' + cId).html(cVal);
                            $("span#" + cId).attr('class', 'editable');
                        }
                        return false;
                    });
                    $('#selectAll').live('click',function () {
                        fnGetSelected(oTable);
                    });
                    $('#deselectAll').live('click',function () {
                        fnGetDeSelected(oTable);
                    });

                    $('.delete').live('click',function () {
                        $("#fnSel").attr('value', $(this).attr('id'));
                        $( "#dialog-confirm" ).dialog( "open" );
                        return false;
                    });
                } );

                $("div.toolbar").html('<span style="float:left; margin:0; padding:0;"><a href="javascript:void(NULL);" id="selectAll">selectAll</a> &nbsp; <a href="javascript:void(NULL);" id="deselectAll">deselectAll</a></span> &nbsp; <select id="select_box" ONCHANGE="oTable.fnFilter(this.options[this.selectedIndex].value);"><option selected disabled>choose error to see</option<option value="suspended">suspended</option><option value="can\'t log in">can\'t log in</option><option value="can\'t find tweet">can\'t find tweet</option><option value="">clear</option></select>');

                function fnGetSelected( oTableLocal )
                {
                    var aReturn = new Array();
                    var aTrs = oTableLocal.fnGetNodes();

                    for ( var i=0 ; i<aTrs.length; i++ )
                    {
                        var an = $("#fnSel").attr('value');
                        var aData = oTableLocal.fnGetData( aTrs[i]);
                        $("#fnSel").attr('value', an + "|" + aData[0]);
                        if ( $(aTrs[i]).hasClass('row_selected') )
                        {
                            aReturn.push( aTrs[i] );
                        } else {
                            $(aTrs[i]).addClass('row_selected');
                        }
                    }
                    return aReturn;
                }
                function fnGetDeSelected( oTableLocal )
                {
                    var aReturn = new Array();
                    var aTrs = oTableLocal.fnGetNodes();
                    $("#fnSel").attr('value', '');
                    for ( var i=0 ; i<aTrs.length; i++ )
                    {
                        if ( $(aTrs[i]).hasClass('row_selected') )
                        {
                            aReturn.push( aTrs[i] );
                            $(aTrs[i]).removeClass('row_selected');
                        }
                    }
                    return aReturn;
                }



            });

        </script>

    </head>
    <body>

        <div id="showMessage" style="display: none;"></div>

        <div id="dialog-confirm" title="Delete chosen accs?">
            <p><span class="ui-icon ui-icon-alert"
                     style="float: left; margin: 0 7px 20px 0;"></span> This accounts will be
                permanently deleted and cannot be recovered. Are you sure?</p>
            <form method="get" action="#">
                <input type="hidden" id="DeleteId" value="0" />
            </form>
        </div>

        <div id="add-accs-dialog" title="Add accounts">
            <p>Paste:</p>
            <small>separate with <strong>:</strong></small>
            <form >
                <textarea id="accList" style="width:520px; height:400px" name="accs"></textarea>
            </form>
        </div>

        <input type="hidden" value="" id="fnSel" />

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
                <li class="selected"><a href='./accounts.php'>Accounts</a></li>
                <li><a href='./proxy.php'>Proxy</a></li>
                <li><a href='./setts.php'>Settings</a></li>
            </ul>
        </div>

        <div id="cont">
            <div id="wrap">
                <div class="boxed">
                    <p class="caption">Accounts</p>
                    <span id="icons">
                        <a href="#addAccs" id="addAccs"><img src='./images/icons/add.png' alt="Add accounts" title="Add accounts"/></a>
                        <a href="#delete" id="delete"><img src='./images/icons/delete.png' alt="Delete account" title="Delete account" /></a>
                        <a href="#saveall" id="saveAll"><img src='./images/icons/save-all.png' alt="Save all edited accounts" title="Save all edited accounts" /></a>
                        <a href="#clearAll" id="clearAll"><img src='./images/icons/cut.png' alt="Clear All" title="Clear All" /></a>
                        <a href="./includes/exportproxy.php" id="export"><img src='./images/icons/export.png' alt="Export" title="Export" /></a>
                        <span class="info tips" style="width:600px;">
                                <?php $hints = array(
            "To edit account pair, click on E icon on the right, edit it and click on Save All",
            "You can edit multiple accounts at once, don't forget click Save All when you finished",
            "To delete all suspended accounts, type in <strong>'susp'</strong> in searchbox and click <strong>'select all'</strong>. then delete it"
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
                                <th>PAIR</th>
                                <th>LAST ERROR</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="dataTables_empty">Loading data from
			server</td>
                            </tr>
                        </tbody>
                        <tfoot>

                            <tr>
                                <th>ID</th>
                                <th>PAIR</th>
                                <th>LAST ERROR</th>
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
