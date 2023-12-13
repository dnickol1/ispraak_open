<?php

/*

This page allows instructors to override iSpraak regional defaults for ASR and TTS settings.
To load, it requires instructor email, token, and action variables in URL query string.

The most recent preferences display if any are saved, but if an instructor makes multiple
saves, these are also stored in the preferences database. 

All settings can be reset by clicking the trash can icon. 

Authentication is required. 

*/

session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");


//Get function from query string for this page
//Not every call to this page will need all these variables

$action=$_GET['action'];
$email=$_GET['email'];
$ispraak_token=$_GET['token'];
$permission = "denied"; 

$asr_pref = "default";
$tts_pref = "default";


//Is access allowed to this page - check for email and token pair and expiry

//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_auth where token='$ispraak_token' AND email='$email'");
$j = 0;
$auth_time=mysqli_result($myresult,$j,"auth_time");
$auth_time_expire = $auth_time + 25200; 
$ispraak_time = time();

if ($auth_time_expire > $ispraak_time)
{
$permission = "good";
}

if ($permission == "good")
{

//connect to the database to see if preferences exist
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);


	if ($action == "update")
	{
		//get update variables from the form
		
		$language=$_POST['element_3'] ?? 'NA';
		$tts_pref=$_POST['element_4'] ?? 'NA';
		$asr_pref=$_POST['element_1'] ?? 'NA';
		$unused = "NA";
	
	
		//define the query
		$query = "INSERT INTO ispraak_user_prefs VALUES ('$email', '$tts_pref', '$asr_pref', '$language','$unused','$unused','')";

		//execute the query and determine for debugging if it was a good insert
		$good_insert = mysqli_query($msi_connect, $query);
		
		//close your connection to the DB
		mysqli_close($msi_connect);

		//redirect

		$newURL = "login.php?action=view&email=$email&token=$ispraak_token";
		header('Location: '.$newURL);

	}


	if ($action == "reset")
	{
		//get update variables from the form
		
		$language=$_POST['element_3'] ?? 'NA';
		$tts_pref=$_POST['element_4'] ?? 'NA';
		$asr_pref=$_POST['element_1'] ?? 'NA';
		$unused = "NA";
	
		//define the query
		$query = "UPDATE ispraak_user_prefs SET email = 'deleted' WHERE email = '$email' "; 

		//execute the query and determine if it was a good insert
		$good_update = mysqli_query($msi_connect, $query);
	
		//close your connection to the DB
		mysqli_close($msi_connect);

		//redirect

		$newURL = "login.php?action=view&email=$email&token=$ispraak_token";
		header('Location: '.$newURL);

	}






//begin output

echo "$ispraak_header <form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"preferences.php?action=update&email=$email&token=$ispraak_token\">$ispraak_logo
			<a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
	
<br><br><br>"; 

echo "The iSpraak default regional settings can be overridden here. Please note that options are only available for some languages and some regions. Be advised that if your region does not match the default language, iSpraak will not function properly. 

<br>
<a href=\"preferences.php?action=reset&email=$email&token=$ispraak_token\"><img src=\"images/trash_it.png\" width=\"30\" align=\"right\"></a>

<br>";


if (mysqli_connect_errno())
{
  	$error_saving_db = "<p style=\"color:red\">Database connection: X</span>";
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}
else
{
//since there is no connection error, prepare input for first query
$email = mysqli_real_escape_string($msi_connect, $email);


$default_description = "None saved"; 
$language = "en"; 
$default_tts_description = "None saved"; 
$default_asr_description = "None saved"; 

//do language preferences exist for this user? 

$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_user_prefs where email='$email' ORDER BY id DESC");
$rowcount=mysqli_num_rows($myresult);	


if ($rowcount < 1)
{
	//echo "<center>No language preferences saved for your account. You can set these below.</center>"; 
}
else
{
	$i = 0;
	$language=mysqli_result($myresult,$i,"language");
	$tts_pref=mysqli_result($myresult,$i,"tts_pref");
	$asr_pref=mysqli_result($myresult,$i,"asr_pref");
	
	$default_description = outputLanguage($language);
	//$default_description = "Preferred: $language"; 
	$default_tts_description = "Preferred: $tts_pref";
	$default_asr_description = "Preferred: $asr_pref"; 


	
	//echo "<ul>Your default language is set to: $language</ul>";
	//echo "<ul>Your TTS preference is set to: $tts_pref</ul>";
	//echo "<ul>Your ASR preference is set to: $asr_pref</ul>";
		
}
	
//do email preferences exist for this user? 
/*
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_unsubscribe where email='$email'");
$rowcount=mysqli_num_rows($myresult);	

if ($rowcount < 1)
{
	//echo "<center>No email preferences are saved for your account. You can set these below.</center>"; 
}
else
{

	$i = 0;
	$email_pref_code=mysqli_result($myresult,$i,"email_pref_code");
	$email_pref_code2=mysqli_result($myresult,$i,"email_pref_code2");

	echo "<ul>Daily Digest Emails: $email_pref_code</ul>";
	echo "<ul>Creator Emails: $email_pref_code2</ul>";	

}
*/

//close your connection to the DB
mysqli_close($msi_connect);


//Now we continue output and display a form with all possible preferences

echo "


<ul>
<li id=\"li_3\" >
<label class=\"description\" for=\"element_3\">Language to override</label>
<div>
<select class=\"element select medium\" id=\"element_3\" name=\"element_3\"> 
<option value=\"$language\" selected=\"selected\">$default_description</option>
<option value=\"am\" >Amharic</option>
<option value=\"ar\" >Arabic</option>
<option value=\"bn\" >Bengali</option>
<option value=\"ca\" >Catalan</option>
<option value=\"zh\" >Chinese</option>
<option value=\"hr\" >Croatian</option>
<option value=\"cs\" >Czech</option>
<option value=\"da\" >Danish</option>
<option value=\"nl\" >Dutch</option>
<option value=\"en\" >English</option>
<option value=\"fi\" >Finnish</option>
<option value=\"fr\" >French</option>
<option value=\"de\" >German</option>
<option value=\"el\" >Greek</option>
<option value=\"he\" >Hebrew</option>
<option value=\"hi\" >Hindi</option>
<option value=\"hu\" >Hungarian</option>
<option value=\"id\" >Indonesian</option>
<option value=\"it\" >Italian</option>
<option value=\"ja\" >Japanese</option>
<option value=\"ko\" >Korean</option>
<option value=\"no\" >Norwegian</option>
<option value=\"fa\" >Persian</option>
<option value=\"pl\" >Polish</option>
<option value=\"pt\" >Portuguese</option>
<option value=\"ro\" >Romanian</option>
<option value=\"ru\" >Russian</option>
<option value=\"es\" >Spanish</option>
<option value=\"sw\" >Swahili</option>
<option value=\"sv\" >Swedish</option>
<option value=\"th\" >Thai</option>
<option value=\"tr\" >Turkish</option>
<option value=\"uk\" >Ukrainian</option>
<option value=\"ur\" >Urdu</option>
<option value=\"vi\" >Vietnamese</option>
<option value=\"zu\" >Zulu</option>

		</select><p class=\"guidelines\" id=\"guide_3\">Select the language you want to override TTS/ASR settings for.</p>
		</div> 
		</li>	
		
		
		<li id=\"li_4\" >
		<label class=\"description\" for=\"element_4\">iSpraak TTS Region</label>
		
<div>
<select class=\"element select medium\" id=\"element_4\" name=\"element_4\"> 
<option value=\"$tts_pref\" selected=\"selected\">$default_tts_description</option>
<option value=\"default\" >Default</option>
<option value=\"yue-HK\">Chinese (Cantonese Hong Kong)</option>
<option value=\"cmn-CN\">Chinese (Mandarin Mainland)</option>
<option value=\"cmn-TW-HK\">Chinese (Taiwan)</option>

<option value=\"nl-NL\" >Dutch (Holland)</option>
<option value=\"nl-BE\" >Dutch (Belgium)</option>

<option value=\"en-US\" >English (USA)</option>
<option value=\"en-GB\" >English (UK)</option>
<option value=\"en-AU\" >English (Australia)</option>
<option value=\"en-IN\" >English (India)</option>


<option value=\"fr-FR\" >French (France)</option>
<option value=\"fr-CA\" >French (Canada)</option>
<option value=\"pt-BR\" >Portuguese (Brazil)</option>
<option value=\"pt-PT\" >Portuguese (Portugal)</option>

<option value=\"es-US\" >Spanish (USA)</option>
<option value=\"es-ES\" >Spanish (Spain)</option>

		</select>		
		
	
<p class=\"guidelines\" id=\"guide_4\">Your selection here will override default region for synthesized voices.</p> 
		</li>	
		
		
		
		<li id=\"li_5\" >
		<label class=\"description\" for=\"element_1\">iSpraak ASR Region</label>
		
<div>
<select class=\"element select medium\" id=\"element_1\" name=\"element_1\"> 
<option value=\"$asr_pref\" selected=\"selected\">$default_asr_description</option>
<option value=\"default\" >Default</option>
<option value=\"ar-DZ\" >Arabic (Algeria)</option>
<option value=\"ar-BH\" >Arabic (Bahrain)</option>
<option value=\"ar-EG\" >Arabic (Egypt)</option>
<option value=\"ar-IQ\" >Arabic (Iraq)</option>
<option value=\"ar-JO\" >Arabic (Jordan)</option>
<option value=\"ar-KW\" >Arabic (Kuwait)</option>
<option value=\"ar-MA\" >Arabic (Morocco)</option>
<option value=\"ar-LB\" >Arabic (Lebanon)</option>
<option value=\"ar-OM\" >Arabic (Oman)</option>
<option value=\"ar-SA\" >Arabic (Saudi Arabia)</option>
<option value=\"ar-TN\" >Arabic (Tunisia)</option>
<option value=\"ar-AE\" >Arabic (United Arab Emirates)</option>
<option value=\"ar-QA\" >Arabic (Qatar)</option>
<option value=\"bn-BD\" >Bengali (Bangladesh)</option>
<option value=\"bn-IN\" >Bengali (India)</option>
<option value=\"yue-Hant-HK\" >Chinese (Cantonese Hong Kong)</option>
<option value=\"cmn-Hans-CN\" >Chinese (Mandarin Mainland)</option>
<option value=\"cmn-Hans-HK\" >Chinese (Mandarin Hong Kong)</option>
<option value=\"cmn-Hant-TW\" >Chinese (Taiwan)</option>
<option value=\"en-AU\" >English (Australia)</option>
<option value=\"en-CA\" >English (Canada)</option>
<option value=\"en-IN\" >English (India)</option>
<option value=\"en-NZ\" >English (New Zealand)</option>
<option value=\"en-ZA\" >English (South Africa)</option>
<option value=\"en-GB\" >English (United Kingdom)</option>
<option value=\"en-US\" >English (USA)</option>
<option value=\"it-IT\" >Italian (Italy)</option>
<option value=\"it-CH\" >Italian (Switzerland)</option>
<option value=\"pt-BR\" >Portuguese (Brazil)</option>
<option value=\"pt-PT\" >Portuguese (Portugal)</option>
<option value=\"es-AR\" >Spanish (Argentina)</option>
<option value=\"es-BO\" >Spanish (Bolivia)</option>
<option value=\"es-CL\" >Spanish (Chile)</option>
<option value=\"es-CO\" >Spanish (Colombia)</option>
<option value=\"es-CR\" >Spanish (Costa Rica)</option>
<option value=\"es-DO\" >Spanish (Dominican Republic)</option>
<option value=\"es-EC\" >Spanish (Ecuador)</option>
<option value=\"es-SV\" >Spanish (El Salvador)</option>
<option value=\"es-GT\" >Spanish (Guatemala)</option>
<option value=\"es-HN\" >Spanish (Honduras)</option>
<option value=\"es-MX\" >Spanish (Mexico)</option>
<option value=\"es-NI\" >Spanish (Nicaragua)</option>
<option value=\"es-PA\" >Spanish (Panama)</option>
<option value=\"es-PY\" >Spanish (Paraguay)</option>
<option value=\"es-PE\" >Spanish (Peru)</option>
<option value=\"es-PR\" >Spanish (Puerto Rico)</option>
<option value=\"es-ES\" >Spanish (Spain)</option>
<option value=\"es-US\" >Spanish (United States)</option>
<option value=\"es-UY\" >Spanish (Uruguay)</option>
<option value=\"es-VE\" >Spanish (Venezuela)</option>
<option value=\"sw-KE\" >Swahili (Kenya)</option>
<option value=\"sw-TZ\" >Swahili (Tanzania)</option>
<option value=\"ur-IN\" >Urdu (India)</option>
<option value=\"ur-PK\" >Urdu (Pakistan)</option>

		</select>		
	
<p class=\"guidelines\" id=\"guide_1\">Changes default, but students can still select their preferred region.</p> 
		</li>	
		
				
		
		
		
		
		
				
					<li class=\"buttons\">
			    <input type=\"hidden\" name=\"form_id\" value=\"1007732\" />
				    <input type=\"hidden\" name=\"email\" id=\"email\" value=\"$email\" />
			       <input type=\"hidden\" name=\"token\" id=\"token\" value=\"$ispraak_token\" />
			    
				<input id=\"saveForm\" class=\"button5\" type=\"submit\" name=\"submit\" value=\"Update Preferences\" />
		</li>
			</ul>

	
			</p>

		</form>	
		$ispraak_footer
	</div>
	</body>
	
</html>";



//above this bracket user is authenticated
}
}

else
{

echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br><center>Sorry, we are unable to authenticate you right now.<br><br><br>
<a href=\"login.php\" class=\"button4\">Login Page</a>
<br>
<br>		</div>		
			</p>

		</form>	
		$ispraak_footer
	</div>
	</body>
</html>";
}


?>

