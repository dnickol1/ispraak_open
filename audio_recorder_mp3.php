<?php

/*

Redirected from makeit.php, this page displays an in-browser audio recorder. 
This recorder uses a JS library to convert from blob to MP3. 
When done, the page redirects to edit.php 

*/


session_start();
//this above line will grant us access to session variables
//but we need local versions for this page

$email = $_SESSION['email']; 
$language = $_SESSION['language']; 
$blocktext = $_SESSION['blocktext']; 
$mykey = $_SESSION['mykey']; 
$mykey2 = $_SESSION['mykey2'];
$filename = $mykey . '_' . $mykey2;

//make a session variable to be used by edit.php
$_SESSION['mp3link'] = $filename . '.mp3';
$_SESSION['filename'] =  $filename; 

echo"

<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>  
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script>
    </head>
 
<body id=\"main_body\">
  
   <img id=\"top\" src=\"images/top.png\">
   <div id=\"form_container\">
   <div id=\"headerBar\"></div>
  
       <form id=\"form_1007732\" class=\"ispraak_form\" enctype=\"multipart/form-data\" method=\"post\" action=\"#\">
                   <div class=\"form_description_alt\">
                  
                   <img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\">
   <br>
    
    
                   <img src = \"images/micro.gif\" id=\"mic\" width=\"100\" align=\"right\"> 
           <img src = \"images/gears.gif\" id=\"upload\" width=\"100\" align=\"right\"> 
           <img src = \"images/gears.gif\" id=\"prep\" width=\"100\" align=\"right\"> 
         
    <br><br>
    
                  
    <section class=\"section\">
    <div class=\"container\">
        
    
       <div class=\"row\">
           <div class=\"col-lg-12\">
           



 
               <b>Record your prompt:</b> $blocktext
               <br>
           </div>
       </div>
           <div class=\"row\">  
                <div class=\"col-lg-12\">
                    <p class=\"start-recording-text\"></p>
                </div>             
                
                
                
                	<div style=\"max-width: 28em;\">

		<select id=\"encodingTypeSelect\" hidden=\"hidden\"><option value=\"mp3\" selected=\"selected\">MP3 (MPEG-1 Audio Layer III) (.mp3)</option></select>
            <input id=\"filename\" value=\"$filename\" hidden=\"hidden\">     
		                
                		<div id=\controls\">
			<button id=\"recordButton\" class=\"button4\">Record</button>
			<button id=\"stopButton\" class=\"button4\" disabled>Stop</button>
			<div id=\"continueButton\"><a href=\"edit.php\" class=\"button4\">Use this audio</a></div>
			<button id=\"restart\" class=\"button4\" onclick=\"location.href='audio_recorder_mp3.php'\">Start over</button>
			
			
			
			 <br>
		</div>
               
		<div id=\"formats\" style=\"display: none;\"></div>
		
		<pre id=\"log\" hidden=\"hidden\"></pre>

		
		<div id=\"recordingsList\"></div>
	</div>


            </div>
        </div>
    </div>

</section>

<script>
document.getElementById('mic').style.display=\"none\";
document.getElementById('upload').style.display=\"none\";
document.getElementById('stopButton').style.display=\"none\";
document.getElementById('continueButton').style.display=\"none\";
document.getElementById('prep').style.display=\"none\";
document.getElementById('restart').style.display=\"none\";
</script>


<script type=\"text/javascript\" src=\"javascript/WebAudioRecorder.min.js\"></script>
<script type=\"text/javascript\" src=\"javascript/app_mp3.js?v=36\"></script>

<br><br>
</body>

</html>";




?>
  
