<?php
require_once './db.php';
require_once './shorteners.php';

$sTable = "tasks";/* DB table to use */
$verbose = true; //write all messages into log
$tryProxy = 3; //proxy checks before change if bad
$proxyCheckUrl = "http://alexvolkov.ru/postcheck.php"; //where to check
$delay = 60; //10 minutes delay, if twitter send 500 errors

$gaSql['link'] = mysql_pconnect($gaSql['server'], $gaSql['user'], $gaSql['password']) or die('Could not open connection to server');
mysql_select_db($gaSql['db'], $gaSql['link']) or die('Could not select database ' . $gaSql['db']);
function _useragent () {
    $user_agents = explode("\n", file_get_contents('../src/user_agents.txt'));
    if (is_array(@$user_agents)) {
        return trim($user_agents[array_rand($user_agents)]);
    } else {
        $s = rand(1, 4);
        switch ($s) {
            case 1:
                $agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)";
                break;
            case 2:
                $agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)";
                break;
            case 3:
                $agent = "Opera/9.20 (Windows NT 6.0; U; en)";
                break;
            case 4:
                $agent = "Mozilla/4.8 [en] (Windows NT 6.0; U)";
                break;
        }
        return $agent;
    }
}
function CheckStatus ($id) {
    $query = mysql_query("SELECT `status` FROM `tasks` WHERE `tasks`.`id` = '$id'");
    $status = mysql_result($query, NULL);
    if (($status !== 'stop')) :
        return false;
    else :
        return true;
    endif;
}
function ChangeStatus ($id, $status) {
    $query = mysql_query("UPDATE `tasks` SET `status` = '$status' WHERE `tasks`.`id` = '$id'");
    if (($query)) :
        return false;
    else :
        return true;
    endif;
}
function SetError ($id, $error) {
    $query = mysql_query("UPDATE `accounts` SET `error` = '$error' WHERE `accounts`.`pair` = '$id'");
    if (($query)) :
        return false;
    else :
        return true;
    endif;
}
function SetErrorProxy ($proxy, $error) {
    $query = mysql_query("UPDATE `proxy` SET `error` = '$error' WHERE `proxy`.`proxy` = '$proxy'");
    if (($query)) :
        return false;
    else :
        return true;
    endif;
}
function ChangeProgress ($id, $percent) {
    $query = mysql_query("UPDATE `tasks` SET `progress` = '$percent' WHERE `tasks`.`id` = '$id'");
    if (($query)) :
        return false;
    else :
        return true;
    endif;
}
function LoadDetails ($id) {
    $query = mysql_query("SELECT * FROM `tasks` WHERE `tasks`.`id` = '$id'");
    while ($row = mysql_fetch_assoc($query)) :
        $localConf[] = $row;
    endwhile
    ;
    $query = mysql_query("SELECT * FROM `config` WHERE 1");
    while ($row = mysql_fetch_assoc($query)) :
        $globalConf[] = $row;
    endwhile
    ;
    return (array_merge($localConf, $globalConf));
}
function LoadAccounts ($limit, $chooseBy, $use_errors) {
    ($use_errors) ?  $errors = "1" : $errors = "error='good' OR error=''";
    ($limit == 0) ? $limit = "" : $limit = "LIMIT " . $limit;
    if ($chooseBy == 'random')
        $query = ("SELECT * FROM `accounts` WHERE $errors ORDER BY RAND() $limit");
    if ($chooseBy == 'order')
        $query = ("SELECT * FROM `accounts` WHERE $errors $limit");
    $query = mysql_query($query);
    while ($row = mysql_fetch_assoc($query)) :
        $res[] = $row;
    endwhile
    ;
    return ($res);
}
function echo_memory_usage() {
    $mem_usage = memory_get_usage(true);

    if ($mem_usage < 1024)
        echo $mem_usage." bytes";
    elseif ($mem_usage < 1048576)
        echo round($mem_usage/1024,2)." kilobytes";
    else
        echo round($mem_usage/1048576,2)." megabytes";

    echo "<br/>";
} 

function LoadProxy ($use_errors, $proxyCheckUrl) {
    $a = true;
    while ($a == true) {
        ($use_errors) ?  $errors = "1" : $errors = "error=''";
        $query = mysql_query("SELECT `proxy` FROM `proxy` WHERE " . $errors . " ;");
        while ($row = mysql_fetch_assoc($query))
            $res[] = trim($row['proxy']);
        if(count($res) < 1)
            die('no proxy');
        shuffle($res);
        $pr = $res[0];
        unset($res);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $proxyCheckUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, "10");
        curl_setopt($ch, CURLOPT_TIMEOUT, "10");
        curl_setopt($ch, CURLOPT_PROXY, $pr);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "proxy=" . $pr);
        $data = curl_exec($ch); //echo $data;
        curl_close($ch);
        preg_match("!" . $pr . "!si", $data, $out);
        if (strlen(@$out[0]) > 1) {

            return $pr;
            $a = false;
            break;
        }
        //set error to proxy
        $query = mysql_query("UPDATE  `proxy` SET `error` = 'error' WHERE `proxy` = '$pr' ");
    }
}
function GetPage ($url, $refer, $postdata) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, '../tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, '../tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if ($postdata) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_URL, $refer);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    }
    $hh = curl_exec($ch);
    return $hh;
}
function Logging ($id, $message) {
    global $verbose;
    $logmess = date("r") . " " . $id . " " . $message . "\r\n";
    if ($verbose == true)
        echo $logmess;
    if (! file_exists('../tmp/' . $id . '.txt'))
        fopen('../tmp/' . $id . '.txt', 'w+');
    $current = file_get_contents('../tmp/' . $id . '.txt');
    file_put_contents('../tmp/' . $id . '.txt', $current . $logmess);
}
function post_text ($ch, $text, $id, $proxy, $login) {
    global $proxyCheckUrl, $useProxy;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_REFERER, "http://twitter.com/");
    curl_setopt($ch, CURLOPT_URL, "http://twitter.com/");
    if ($useProxy) {
        curl_setopt($ch, CURLOPT_PROXY, @$proxy);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, "40");
        curl_setopt($ch, CURLOPT_TIMEOUT, "40");
    } else {
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, "10");
        curl_setopt($ch, CURLOPT_TIMEOUT, "10");
    }
    $home = curl_exec($ch);
    if (@$home) {
        $authenticity_token = null;
        preg_match("#<input name=\"authenticity_token\" value=\"(.+)\"#sU", $home, $authenticity_token);
        if (@$authenticity_token[1]) {
            Logging($id, "token for tweeting found " . $authenticity_token[1]);
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($useProxy) {
                curl_setopt($ch, CURLOPT_PROXY, @$proxy);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, "40");
                curl_setopt($ch, CURLOPT_TIMEOUT, "40");
            } else {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, "10");
                curl_setopt($ch, CURLOPT_TIMEOUT, "10");
            }
            curl_setopt($ch, CURLOPT_REFERER, "http://twitter.com/");
            curl_setopt($ch, CURLOPT_URL, "http://twitter.com/status/update");
            curl_setopt($ch, CURLOPT_POSTFIELDS, "authenticity_token=" . $authenticity_token[1] . "&status=" . urlencode($text . ".") . "&twttr=true&return_rendered_status=true");
            $page = curl_exec($ch);
            curl_close($ch);
            preg_match("!<body>You are being(.*?)<\/body>!si", $page, $out);
            $page = preg_replace("/\r/", "", $page);
            $page = preg_replace("/\n/", "", $page);
            preg_match("!<a id=\"status_star_([0-9]+)\" class=\"fav-action non-fav\" title=\"favorite this tweet\">!si", $page, $check);
            if(strlen($check[1]) > 1)
                Logging($id, "good one: http://twitter.com/" . $login . "/status/" . $check[1]);
            $text = explode(" ", $text);
            if (strpos($page, $text[0]) > 1) :
                return true;
            else :
                return false;
            endif;
        }
    }
    return false;
}

function LoadPage($url, $postdata, $proxy) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    if ($proxy) {
        Logging(NULL, "switch to proxy " . $proxy . ", timeout limit up to 60 seconds");
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, "60");
        curl_setopt($ch, CURLOPT_TIMEOUT, "60");
    } else {
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, "30");
        curl_setopt($ch, CURLOPT_TIMEOUT, "30");
    }
    curl_setopt($ch, CURLOPT_ENCODING , "gzip");
    curl_setopt($ch, CURLOPT_COOKIEFILE, '../tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, '../tmp/cookie.txt');
    curl_setopt($ch, CURLOPT_USERAGENT, _useragent());
    if($postdata) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    return curl_exec($ch);
}

function TweetCheck ($text) {
    preg_match("!http:\/\/(.*?)=!si", $text, $out);
    if (strlen(@$out[1]) > 1) :
        //link found in text
        $text = preg_replace("!http://" . $out[1] . "!", "", $text);
        $wordLimit = 140 - strlen(' http://' . $out[1]);
        $text = substr($text, 0, $wordLimit);
        $text = $text . 'http://' . $out[1];
    else :
        $text = substr($text, 0, 140);
    endif;
    return preg_replace("!=!si", " ", $text);
}
function derPoster ($id) //ja ja, naturlich!
{
    global $tryProxy, $proxyCheckUrl, $delay;
    Logging($id, "task started\r\n\r\n");
    $cPos = 0;
    $addCheck = 0; //additional check for proxy failing

    $confs = LoadDetails($id);
    if ( file_exists('../tmp/' . $id . '.txt'))
        unlink('../tmp/' . $id . '.txt');

    $lConf = $confs[0];
    (($confs[1]['opt_value'] == 'on')) ? $useShort = true : $useShort = false;
    (($confs[2]['opt_value'] == 'on')) ? $useProxy = true : $useProxy = false;
    (($confs[5]['opt_value'] == 'on')) ? $use_proxy_error = true : $use_proxy_error = false;
    (($confs[6]['opt_value'] == 'on')) ? $use_accs_error = true : $use_accs_error = false;
    ChangeStatus($id, "start");
    $short = new Shorteners();
    switch ($lConf['source']) {
        case ('feeds'):
            Logging($id, "getting feeds");
            $feeds = explode("\n", $lConf['content']);
            foreach ($feeds as $feed) {
                if (strlen($feed) > 0) {
                    Logging($id, "work with " . $feed);
                    //забираем фид
                    $feedText = GetPage($feed, NULL, NULL);
                    //разбираем ссылки
                    preg_match_all("!<title>(.*?)<\/title>!si", $feedText, $feedOutTitle);
                    preg_match_all("!<link>(.*?)<\/link>!si", $feedText, $feedOutLink);
                    $feedOutTitle = array_slice($feedOutTitle[1], 1);
                    $feedOutLink = array_slice($feedOutLink[1], 1);
                    //постим их как список ссылок
                    foreach ($feedOutLink as $num => $fLink) :
                        if ($useShort) {
                            $txt = $feedOutTitle[$num] . ' ' . $short->$lConf['shortener']($fLink) . '=';
                        } else {
                            $txt = $feedOutTitle[$num] . ' ' . $fLink . '=';
                        }
                        $linkArray[] = TweetCheck($txt);
                    endforeach
                    ;
                }
            }
            Logging($id, "formed " . count($linkArray) . " items in post array");
            break;
        case ('tweets'):
            Logging($id, "getting tweets");
            $tweets = explode("\n", $lConf['content']);
            foreach ($tweets as $tweet) {
                if (strlen($tweet) > 0) {
                    preg_match("!http://(.*)!si", $tweet, $out);
                    if (strlen(@$out[0]) > 1) {
                        $fLink = trim($out[0]);
                        $tweet = preg_replace("!" . preg_quote($fLink) . "!si", "", $tweet);
                        if ($useShort) {
                            $txt = trim($tweet) . '' . $short->any($fLink) . '=';
                        }
                        if (! $useShort) {
                            $txt = trim($tweet) . '' . $fLink . '=';
                        }
                    } else {
                        $txt = $tweet . '=';
                    }
                    $linkArray[] = TweetCheck($txt);
                }
            }
            Logging($id, "formed " . count($linkArray) . " items in post array");
            break;
    }
    //posting
    foreach($linkArray as $checks) {
        if(strlen($checks) > 1)
            $tmpArr[] = $checks;

    }
    $linkArray = $tmpArr;
    $ord = $lConf['ordering'];
    $acc = $lConf['used_accounts'];
    $accounts = LoadAccounts($acc, $ord, $use_accs_error);
    $textPosition = 0;

    //proxy and account check
    if(count($accounts) < 1)
        Logging($id, "check your accounts!");



    for ($i = 0; $i < count($accounts); $i++) {
        if( CheckStatus($id) == true) {
            Logging($id, "task paused or deleted");
            die();
        }

        if ($useProxy) {
            $cProxy = LoadProxy($use_proxy_error, $proxyCheckUrl);
            //Logging($id, "proxy used - " . $cProxy);
            if(strlen($cProxy) < 3)
                Logging($id, "check your proxy!");
        }

        if ( file_exists('../tmp/cookie.txt'))
            unlink('../tmp/cookie.txt');

        //Logging($id, "cookie deleted");

        unset($authenticity_token);
        if ($textPosition > (count($linkArray) - 1))
            $textPosition = 0;
        $text = $linkArray[$textPosition];
        $textPosition ++;
        $cPos ++;
        if ($cPos == count($linkArray))
            $cPos = 0;
        //calculating percents
        $chunk = 100 / count($accounts);
        $percent = $chunk * $i;
        $percent = $percent + $chunk;
        ChangeProgress($id, $percent);
        Logging($id, "use " . $accounts[$i]['pair']);
        $exp = explode(":", $accounts[$i]['pair']);
        //$i++;
        $tryProxyCount = 0;
        while (($tryProxyCount != $tryProxy) || ($tryProxyCount != true)) :

            $twhom = LoadPage("http://mobile.twitter.com/".$exp[0], NULL, $cProxy);
            //Logging($id, "returned size of http://twitter.com/ " . strlen($twhom));
            $authenticity_token = null;


            preg_match("!the profile you were trying to view was suspended due to strange activity!si", $twhom, $out);
            if ($out) { //check for suspend
                //mark in database for suspend
                Logging($id, $i . " account ".$exp[0]." suspended, start new cycle");
                SetError($accounts[$i]['pair'], 'suspended');
                continue(2);
            }

            preg_match("!".$exp[0]."!si", $twhom, $out); //check for success login
            if ($out) {
                Logging($id, "account good, trying to logg in");
            }

            preg_match("#<input name=\"authenticity_token\" type=\"hidden\" value=\"(.+)\"#sU", $twhom, $authenticity_token);

            if (strlen(@$authenticity_token[1]) > 1) {
                //Logging($id, "token found " . $authenticity_token[1]);
                $tryProxyCount = true;
            } else {
                $tryProxyCount ++;
                Logging($id, "Zero answer received, try " . ($tryProxyCount) . " attempt");
        }

        endwhile;

        if (strlen(@$authenticity_token[1]) < 1) {
            Logging($id, "token not found, maybe proxy doesn't work?" . $addCheck);
            $i --;
            $addCheck ++;
            if($addCheck == 3) {
                $addCheck = 0;
                Logging($id, "something wrong with twitter".  $twhom);
                $i++;
                sleep($delay);
                continue;
            }
            continue;
        } else {
            $addCheck = 0;
        }
        $tryProxyCount = 0;
        while (($tryProxyCount != $tryProxy) || ($tryProxyCount != true)) :
            $postvars = array(
                    'authenticity_token' => trim($authenticity_token[1]),
                    'username' => trim($exp[0]),
                    'password' => trim($exp[1]),
            );
            $hh = LoadPage("https://mobile.twitter.com/session", http_build_query($postvars), $cProxy);


            preg_match("!What's happening!si", $hh, $out); //check for success login
            if ($out) {
                //SetError($accounts[$i]['pair'], 'good');
                //Logging($id, "logged in succesfully, trying to post in");
            }

            if (strlen($hh) > 200) {
                $tryProxyCount = true;
            } else {
                $tryProxyCount ++;
                Logging($id, "Trying to resend login to session page, try " . ($tryProxyCount) . " attempt");
        }
        endwhile;

        if ($tryProxyCount != true) {
            Logging($id, "cannot load session page, maybe proxy doesn't work?" . $addCheck);
            $i --;
            $addCheck ++;
            if($addCheck == 3) {
                $addCheck = 0;
                Logging($id, "something wrong with twitter".  $hh);
                $i++;
                sleep($delay);
                continue;
            }
            continue;
        } else {
            $addCheck = 0;
        }
        $tryProxyCount = 0;

        while (($tryProxyCount != $tryProxy) || ($tryProxyCount != true)) :

            $postvars = array(
                    'authenticity_token' => trim($authenticity_token[1]),
                    'tweet[text]' => $text,
                    'tweet[in_reply_to_status_id]' => '',
                    'tweet[lat]' => '',
                    'tweet[long]' => '',
                    'tweet[place_id]' => '',
                    'tweet[display_coordinates]' => '',
            );
            $twe = LoadPage("http://mobile.twitter.com/", http_build_query($postvars), $cProxy);
            if ($twe) {
                $tryProxyCount = true;
                preg_match("!".trim($text)."!si", strip_tags($twe), $out);

                if($out) {
                    //Logging($id, "posted succesfully " . trim($text));
                    //trying to find link dircetly to status
                    preg_match("!<a href=\"/".$exp[0]."/status/([0-9]+.)\" class=\"status_link\">!si", $twe, $cho);
                    if(strlen($cho[1]) > 1) {
                        Logging($id,  "http://twitter.com/".$exp[0]."/status/".$cho[1]);
                        $goodLnk .= "http://twitter.com/".$exp[0]."/status/".$cho[1];
                        $tmf = fopen('../tmp/' . $id . '-good.txt', 'w+');
                        fwrite($tmf, $goodLnk . "\r\n");
                        fclose($tmf);
                    }
                    SetError($accounts[$i]['pair'], 'good');
                    break;
                }



            } else {
                $tryProxyCount ++;
                Logging($id, "can't load page after posting, " . $tryProxyCount . " attempt");
        }
        endwhile;

    }
    ChangeStatus($id, "stop");
}

if (! $argv[1])
    die("no task");

derPoster($argv[1]);
//derPoster($_GET['id']);
mysql_close($gaSql['link']);
?>
