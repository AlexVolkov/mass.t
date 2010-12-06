<?php
class Shorteners
{
    public function bitly ($url)
    {
        $connectURL = 'http://api.bit.ly/v3/shorten?login=masstest&apiKey=R_44403eb439622dd59ba2598255f30824&uri=' . urlencode($url) . '&format=txt'; 
        return $this->curl_get_result($connectURL, NULL, FALSE);
    }
    public function googl ($url)
    {
        $connectURL = 'http://goo.gl/api/shorten';
        $post_fields = array("security_token" => "null", 
                               "url" => $url 
                                        ); 
        return $this->curl_get_result($connectURL, $post_fields, TRUE);
    }
    public function any ($url)
    {
        $func = array('bitly', 'googl');
        $sh = $func[array_rand($func, 1)];
        return $this->$sh($url);
    }
    public function curl_get_result ($url, $postdata, $JSON)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if(isset($postdata)){
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $postdata) ); 
        }
        $data = curl_exec($ch);
        curl_close($ch); 
        if($JSON){
            $obj = json_decode($data);
            return $obj->{'short_url'};
        } else {
             return $data;
        }
        
    }


}
?>


