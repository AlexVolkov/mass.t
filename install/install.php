<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name='robots' content='all, nofollow' />
        <title>TwindeXator</title>
        <link href="../css/login.css" rel="stylesheet" type="text/css" />
        <link href="../css/login-blue.css" rel="stylesheet" type="text/css" />
        <style>
            h2 {color:#fff; text-align:center;}
            a, a:visited, a:active, a:link  {color:#fff;float:left;font-size:18px;margin:10px 5px;}
            p {color:#fff; margin:7px; font-size: 14px; float: left; width: 100%}
            #license textarea{background: #0F1E4A; border: none; color:#fff; width:100%; height:500px; font-size:100%}
            #server {margin:15px 0 0; float:left;}
            #server li label{color:#fff;float:left;margin:5px 10px 0;text-align:right;width:140px; font-size: 16px}
            #server li input{font-size: 18px;}
            .warn{font-size: 16px; color: #cecece;}
        </style>
    </head>
    <body>
        <div id="main" style="padding:50px 0 0">
            <div id="content">
                <div id="login">

                    <div id="logo"><span>TwindeXator</span></div>


                    <?php
                    function ShowLicense() {
                        echo '<form method="get" action="./install.php?step=2" id="license">
                            <h2>End-user license agreement (EULA)</h2>
                            <textarea>Bla-bla-bla</textarea>
                            <input type="submit" name="step" value="Agree"/>
                           </form>';
                    }

                    function ShowForm() {
                        echo '<form method="post" id="server">
                            <ul>
                                <li><label>Host</label><input type="text" value="localhost" name="dbhost" /></li>
                                <li><label>Database</label><input type="text" value="" name="dbname" /></li>
                                <li><label>User</label><input type="text" value="" name="dbuser" /></li>
                                <li><label>Password</label><input type="password" value="" name="dbpass" /></li>
                                <li><input type="submit" value="Send" /></li>
                            </ul>
                           </form>
            ';
                    }

                    function CheckServer() {

                        $a = chmod('../tmp/', 0777);
                        if($a)
                            $a = chmod('../includes/', 0777);
                        echo '<h2>Step 2</h2>';
                        echo '<p>tmp directory is ';
                        echo (is_writable('../tmp/')) ? ' <span style="color:yellow"> writable</span></p>' : '<span style="color:red"> not writable</span></p>';
                        echo '<p>includes directory is ';
                        echo (is_writable('../includes/')) ? ' <span style="color:yellow"> writable</span></p>' : '<span style="color:red"> not writable</span></p>';
                        if($_POST) {
                            $link = mysql_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass']);
                            if(!$link) {
                                ShowForm();
                                die('<p class="warn">Check your settings, cannot establish conection with db.</p>');
                            } else {
                                mysql_select_db($_POST['dbname']);
                                $sql = file_get_contents('../src/sql.sql');
                                if(strlen($sql) < 10)
                                    die('<p class="warn">Cannot open sql file</p>');
                                $sql = explode(";", $sql);
                                foreach ($sql as $req):
                                    if(strlen($req) > 2) {
                                        $query = mysql_query($req);
                                        if(!$query) {
                                            ShowForm();
                                            die('<p class="warn">Cannot execute sql<br /> '.mysql_error().'</p>');
                                        }
                                }
                                endforeach;
                                echo '<p>Database import successfully <span style="color:yellow">completed</span>. <br/>
                                    <a href="../index.php">Finish</a></p>';
                                //write file
                                //import sql
                                $file = @fopen('../includes/db.php', 'a');
                                fwrite($file, '<?php
    $gaSql[\'user\']       = "'.$_POST['dbuser'].'";
    $gaSql[\'password\']   = "'.$_POST['dbpass'].'";
    $gaSql[\'db\']         = "'.$_POST['dbname'].'";
    $gaSql[\'server\']     = "'.$_POST['dbhost'].'";
?>');
                                fclose($file);
                            }

                        } else {
                            ShowForm();
                        }
                    }

                    $step = $_GET['step'];
                    if(!$step)
                        $step = 1;
                    switch($step) {
                        case 1:
                            ShowLicense();
                            break;
                        case Agree:
                            CheckServer();
                            break;

                    }


                    ?>

                </div>
            </div><!-- /content -->
        </div><!-- /main -->
    </body>
</html>