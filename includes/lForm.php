<?php
	function login($mess){

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
  <head>
    <meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
    <meta name='robots' content='all, nofollow' />
    <title>TwindeXator</title>   
    <link href=\"./css/login.css\" rel=\"stylesheet\" type=\"text/css\" />
    <link href=\"./css/login-blue.css\" rel=\"stylesheet\" type=\"text/css\" />  
  </head>
  <body>
  <div id=\"main\">
    <div id=\"content\">
      <div id=\"login\">
        
        <div id=\"logo\"><span>TwindeXator</span></div>

                
        <form method=\"post\" id=\"form-login\" class=\"formBox\">
          <fieldset>
            <div class=\"form-col\">
                <label for=\"username\" class=\"lab\">Key:</label>
                <input type=\"text\" name=\"key\" class=\"input\" id=\"username\" />
            </div>
            <div class=\"form-col form-col-right\"> 
              <input type=\"submit\" name=\"\" value=\"Enter\" class=\"submit\" />
            </div>      
            <div class=\"form-col form-col-check\">". $mess  ."         
            </div>           
          </fieldset>
        </form>

        
      </div>
    </div><!-- /content -->    
  </div><!-- /main -->
  </body>
</html>";
}

?>
