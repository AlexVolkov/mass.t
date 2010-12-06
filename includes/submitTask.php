<?php

include_once './db.php';
include_once './messages.php';
$link = mysql_connect($gaSql['server'], $gaSql['user'], $gaSql['password']);
if (! $link) {
    ShowWindow('Could not connect: ' . mysql_error(), 'error');
    die();
}

function TweetCheck ($text)
{
    preg_match("!http://(.*?) !si", $text, $out);
    if (strlen($out[1]) > 1) : 
        //link found in text
        $text = preg_replace("/(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/", "", $text);
        $wordLimit = 140 - strlen($out[0]);
        $text = substr($text, 0, $wordLimit);
        $text = $text . ' http://' . $out[1];

     else : 
        $text = substr($text, 0, 140);
    endif;
    return $text;
}

mysql_select_db($gaSql['db'], $link);

if(($_POST["numaccs"] == "0")||($_POST["numaccs"] == "")){
		//ShowWindow("You need to set more than 0 accounts", "error");
		//die();
	}

if (strlen($_POST['tweets']) > 0) :
    $tweets = explode("\n", $_POST['tweets']);
    foreach($tweets as $text):
        $res .= TweetCheck($text)."\n";
    endforeach;

    $query = mysql_query("
        	INSERT INTO tasks (`id`, `source`, `used_accounts`, `ordering`, `progress`, `content`, `status`, `shortener`)
        	VALUES ('', 'tweets', '$_POST[numaccs]', '$_POST[radio]', '0', '$res', 'stop', '$_POST[shortener]')
        	");
    ShowWindow("Task added", "success");
    exit();
    endif;

if (strlen($_POST['feeds']) > 0) :
    $tweets = explode("\n", $_POST['feeds']);
    foreach($tweets as $text):
        $res .= $text."\n";
    endforeach;

    $query = mysql_query("
        	INSERT INTO tasks (`id`, `source`, `used_accounts`, `ordering`, `progress`, `content`, `status`, `shortener`)
        	VALUES ('', 'feeds', '$_POST[numaccs]', '$_POST[radio]', '0', '$res', 'stop', '$_POST[shortener]')
        	");
    ShowWindow("Task added", "success");
    exit();
    endif;

mysql_close($link);
ShowWindow("Enter tweet of feed url", "error");
?>
