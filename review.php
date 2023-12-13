<?php

/*

This page displays the model text, audio playback options, and the microphone and transcription box.
Students are redirected here from ispraak.php 
This pages assumes mykey, mkey2, student name, student email are available
This page has an embedded iframe for asr_languages.html which directs to check_for_errors.php 

*/

//Starts or continues session from previous PHP page
session_start();

//Get config file variables and functions
include_once("../../config_ispraak.php");

//The audio player on this page was adapted under MIT license from 
//https://github.com/greghub/green-audio-player

//Connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}

//Get mykey from query string & declare session variable
$mykey=$_GET['mykey'];
$_SESSION['mykey']=$mykey;
$mykey2=$_GET['mykey2'];
$_SESSION['mykey2']=$mykey2;

//Get student info from the form & declare session variables
$student_name=$_POST['student_name'];
$student_email=$_POST['student_email'];

//check for query string for anonymous submission
//and define student name and email as ANON

$mykey3=$_GET['mykey3'];

if  ($mykey3 == "anonymous")
{
	$student_name="iSpraak Guest"; 
	$student_email="guest@ispraak.net";
}

//if this is a set of activities, do not require student to login again, just pull variables
//from active session 

if  ($mykey3 == "set")
{
	$student_name=$_SESSION['start_name'];
	$student_email=$_SESSION['start_email'];
}

//this may be an LTI request, so get info from cookies
$origin=$_GET['entry'];

if ($origin==="808")
{
	$student_name = $_COOKIE["lis_person_name_full"]; 
	$student_email = $_COOKIE["lis_person_contact_email_primary"]; 
	
	//also a mykey cookie but its in the query string anyway
}

//declare session variables for student name and email

$_SESSION['student_name'] = $student_name;
$_SESSION['student_email'] = $student_email;

//Save students trouble from logging in multiple times for each activity
//if they are just being e-mailed links and are not logged in otherwise

$_SESSION['start_name'] = $student_name;
$_SESSION['start_email'] = $student_email;

//Get variables from iSpraak table

$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey='$mykey' AND mykey2='$mykey2'");
$row = mysqli_fetch_array($myresult);
$a=$row["language"];
$b=$row["audiofile"];
$c=$row["blocktext"];
$d=$row["email"];

//Adjust for apostrophes in blocktext that cause audio synthesis issues

$c_stripped = str_replace("\""," ",$c); 


//Create local cookies for javascript calls OR in case session variable breaks
 
setcookie("cookie_mykey", $mykey, time()+7200, '/'); 
setcookie("cookie_mykey2", $mykey2, time()+7200, '/'); 
setcookie("cookie_instructor_email", $d, time()+7200, '/'); 
setcookie("cookie_block_text", $c, time()+7200, '/'); 
setcookie("cookie_language", $a, time()+7200, '/'); 
setcookie("cookie_student_name", $student_name, time()+7200, '/'); 
setcookie("cookie_student_email", $student_email, time()+7200, '/'); 

//check for an instructor regional ASR override on the following languages

$mylang = $a; 
$instructor_email = $d; 
$note = "NA"; 
setcookie("asr_override", $note, time()+7200, '/'); 
	
if ($mylang == "ar" || $mylang == "zh" || $mylang == "it" ||  $mylang == "en" ||  $mylang == "pt" || $mylang == "es" || $mylang == "sw" || $mylang == "ur")
{

	//do language preferences exist for this user? 
	$asr_result = mysqli_query($msi_connect, "SELECT * FROM ispraak_user_prefs where email='$instructor_email' AND language='$mylang' ORDER BY id DESC");
	$asr_rowcount=mysqli_num_rows($asr_result);	
	if ($asr_rowcount > 0)
	{
			$i==0; 
			$asr_pref=mysqli_result($asr_result,$i,"asr_pref");
			$note = "User lang pref set for $mylang for $asr_pref for $instructor_email"; 
			setcookie("asr_override", $asr_pref, time()+7200, '/'); 
	}

}

//See if Google can javascript synth the voice, assume it cannot

$synth = "none";
$synth_text = ""; 
$synth_button = "";

//Decide to show iSpeech synth buttons or not
//Assume that it SHOULD be displayed and turn it OFF for languages it won't work
//iSpeech is deactivated by the followin call - $i_speech = ""; 

//To safely send the btext variable over a query string, we must encode that string
$encoded_btext = urlencode($c_stripped);

//iSpeech is the TTS vendor on iSpraak.com and Google is the vendor on iSpraak.net

$mylang = $a; 
$i_speech = ""; 
$i_speech = "<iframe src=\"premium_tri.php?lang=$mylang&mykey=$mykey&mykey2=$mykey2&btext=$encoded_btext\" align=\"right\" width=\"135\" height=\"50\" scrolling=\"no\" frameBorder=\"0\"></iframe>";

//arabic needs to have this iframe aligned left because it is a right-to-left language
//and otherwise causes display problems
//this is probably true for the other RTL languages, need to confirm! 

$i_speech_arabic = "<iframe src=\"premium_tri.php?lang=$mylang&mykey=$mykey&btext=$encoded_btext\" align=\"left\" width=\"135\" height=\"50\" scrolling=\"no\" frameBorder=\"0\"></iframe>";

//Make instructor e-mail into session variable

$_SESSION['instructor_email'] = $d;

//Make the blocktext a session variable for check-errors.php

$_SESSION['block_text'] = $row["blocktext"];

//Figure out correct language codes for Forvo & Google Default

$_SESSION['language'] = $row["language"];

$mylang = $row["language"];

$iframe_text = "<br><br>Error: Unable to determine source language!<br><br>";


/*

testing without the synth or ispeech adjustments sept 2022

if ($mylang == "fr") { $synth = "fr-FR"; }
if ($mylang == "it") { $synth = "it-IT";}
if ($mylang == "es") { $synth = "es-MX"; }
if ($mylang == "de") { $synth = "de-DE"; }
if ($mylang == "en") { $synth = "en-US"; }

//turn off iSpeech for Vietnamese, Hindi, & Croatian

if ($mylang == "hi") { $synth = "hi-IN"; $i_speech = ""; }

//turn off iSpeech AND browser synth for Amharic, Croatian, Swahili, Vietnamese, Zulu, etc.

if ($mylang == "am") { $i_speech = ""; }
if ($mylang == "hr") { $i_speech = ""; }
if ($mylang == "sw") { $i_speech = ""; }
if ($mylang == "vi") { $i_speech = ""; }
if ($mylang == "zu") { $i_speech = ""; }

if ($mylang == "ur") { $i_speech = ""; }
if ($mylang == "uk") { $i_speech = ""; }
if ($mylang == "bn") { $i_speech = ""; }
if ($mylang == "id") { $i_speech = ""; }
if ($mylang == "ro") { $i_speech = ""; }

*/

if ($mylang == "am" || $mylang == "hr" || $mylang == "fa" || $mylang == "sw" || $mylang == "ur" || $mylang == "zu")
{
	$i_speech = "";
}

//Hebrew and Farsi and Urdu adjustments for RTL language 

if ($mylang == "he") 
{ 
//also change $c variable to include align right text
$before_c = "<table width=\"600\" border=\"0\"><tr><td align=\"right\"><div class=\"ex1\">";
$after_c = "</div></td></tr></table></p>";
$c = $before_c . $c . $after_c;
}



if ($mylang == "fa")
{
//also change $c variable to include align right text
$before_c = "<table width=\"600\" border=\"0\"><tr><td align=\"right\"><div class=\"ex1\">";
$after_c = "</div></td></tr></table></p>";
$c = $before_c . $c . $after_c;
}

if ($mylang == "ur") 
{ 
//also change $c variable to include align right text
$before_c = "<table width=\"600\" border=\"0\"><tr><td align=\"right\"><div class=\"ex1\">";
$after_c = "</div></td></tr></table></p>";
$c = $before_c . $c . $after_c;
}

//Chinese has a transliteration option for pinyin through Glosbe

$zhtext = "";
$pinyin = ""; 


if ($mylang == "he")
{
$bt = $_SESSION['block_text']; 
//$pinyin = transliterator_transliterate('Any-Latin; Hiragana-Latin; Lower();', $bt);
$api_key = $azure_api_tranliteration_key;
$pinyin = api_Transliterate($mylang, $bt, $api_key);

$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}



if ($mylang == "zh") 
{
//$synth = "zh-CN";
$bt = $_SESSION['block_text']; 
//$bt = urlencode ($bt); 
//$json = file_get_contents("https://glosbe.com/transliteration/api?from=Han&dest=Latin&text=$bt&format=json");
//$obj = json_decode($json);
//$pinyin = $obj->{'text'}; 
//$pinyin2 = $obj->{'result'};
//$pinyin = transliterator_transliterate('Any-Latin; Accents-Any; Lower();', $bt);

$pinyin = transliterator_transliterate('Any-Latin; Any-zh; Lower();', $bt);


$zhtext = "<img alt=\"iSpraak\" src=\"images/pinyin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}

//Russian has a transliteration option for Latin text through Glosbe

if ($mylang == "ru")
{
$bt = $_SESSION['block_text']; 
//$bt = urlencode ($bt); 
//$json = file_get_contents("https://glosbe.com/transliteration/api?from=Cyrillic&dest=Latin&text=$bt&format=json");
//$obj = json_decode($json);
//$pinyin = $obj->{'text'}; 
//$pinyin2 = $obj->{'result'};
$pinyin = transliterator_transliterate('Any-Latin; Russian-Latin/BGN; Lower();', $bt);

$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}


//Thai has a transliteration option

if ($mylang == "th")
{
$bt = $_SESSION['block_text']; 
$pinyin = transliterator_transliterate('Any-Latin; Thai-Latin; Lower();', $bt);
$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}

//Greek has a transliteration option

if ($mylang == "el")
{
$bt = $_SESSION['block_text']; 
$pinyin = transliterator_transliterate('Any-Latin; Greek-Latin; Lower();', $bt);
$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}

//Japanese has a transliteration option from Azure API 

if ($mylang == "ja")
{
$bt = $_SESSION['block_text']; 
//$pinyin = transliterator_transliterate('Any-Latin; Hiragana-Latin; Lower();', $bt);
$api_key = $azure_api_tranliteration_key;
$pinyin = api_Transliterate($mylang, $bt, $api_key);

$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}

//Korean has a transliteration option

if ($mylang == "ko")
{
$bt = $_SESSION['block_text']; 
$pinyin = transliterator_transliterate('Any-Latin; Korean-Latin/BGN; Lower();', $bt);
$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}

//Bengali has a transliteration option

if ($mylang == "bn")
{
$bt = $_SESSION['block_text']; 
$pinyin = transliterator_transliterate('Any-Latin; Bengali-Devanagari; Lower();', $bt);
$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}

//Amharic has a transliteration option

if ($mylang == "am")
{
$bt = $_SESSION['block_text']; 
$pinyin = transliterator_transliterate('Any-Latin; Amharic-Latin/BGN; Lower();', $bt);
$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}

//Arabic has a transliteration option

if ($mylang == "ar")
{
$bt = $_SESSION['block_text']; 
//$pinyin = transliterator_transliterate('Any-Latin; Arabic-Latin; Lower();', $bt);

$api_key = $azure_api_tranliteration_key;
$pinyin = api_Transliterate($mylang, $bt, $api_key);

$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}


//Ukrainian has a transliteration option

if ($mylang == "uk")
{
$bt = $_SESSION['block_text']; 
$pinyin = transliterator_transliterate('Any-Latin; Ukrainian-Latin/BGN; Lower();', $bt);
$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}


//Hindi has a transliteration option

if ($mylang == "hi")
{
$bt = $_SESSION['block_text']; 
$pinyin = transliterator_transliterate('Any-Latin; Devanagari-Latin; Lower();', $bt);
$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}

//Persian Farsi has a transliteration option

if ($mylang == "fa")
{
$bt = $_SESSION['block_text']; 
$pinyin = transliterator_transliterate('Any-Latin; Persian-Latin/BGN; Lower();', $bt);
$zhtext = "<img alt=\"iSpraak\" src=\"images/latin.png\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction5()\">"; 
}



//trouble accessing transliteration API as of April 2022
//$pinyin = "Sorry. The transliteration feature is currently unavailable.";

/*

eliminating javascript synth for now

if ($mylang == "ja") { $synth = "ja-JP"; }
if ($mylang == "ko") { $synth = "ko-KR"; }
if ($mylang == "pt") { $synth = "none"; }
if ($mylang == "pl") { $synth = "none"; }


*/



if ($mylang == "ar")
{
$iframe_text = "<iframe src=\"languages/arabic_sa.html\" width=\"600\" height=\"340\" scrolling=\"no\" frameBorder=\"0\"></iframe>";
$before_c = "<table width=\"600\" border=\"0\"><tr><td align=\"right\"><div class=\"ex1\">";
$after_c = "</div></td></tr></table></p>";
$c = $before_c . $c . $after_c;
}

if ($mylang == "el") { $synth = "none"; }
if ($mylang == "tr") { $synth = "none"; }
if ($mylang == "nl") { $synth = "none"; }
if ($mylang == "ca") { $synth = "none"; }
if ($mylang == "cs") { $synth = "none"; }
if ($mylang == "sv") { $synth = "none"; }


//If a TTS file already exists in the directory, we are going to turn off the TTS icons
$tts_file_exists= 'audio/'.$mykey.'_'.$mykey2.'.mp3'; 
//echo "debug: $tts_file_exists";

if (file_exists($tts_file_exists))
{
//file confirmed to reside on server, do not regenerate it
//rather just put it into the default audio player
$b = '../'.$tts_file_exists;
//echo "debug: b"; 

}



$player_text = " ";


if ($b == 1)
{
//Do not show the Green-Audio-Player before the TTS is generated
//Eventually show / hide this element based on existence of audio file
//$player_text = "<div id=\"tts_not_available\" style=\"display:none;\"><i>Reload this page to see iSpraak player.</i></div> ";
}

//Decide if we should display the MP3 audio player or not
//if the variable B is = 2, then a display it


if ($b !== "1")
{
//$player_text = "<br><EMBED SRC=\"http://phrants.net/ispraak/uploadmp3/$b\" HEIGHT=30 WIDTH=200><br>";

//$player_text = "<audio controls><source src=\"uploadmp3/$b\" type=\"audio/mp3\">Your browser does not support the audio playback element.</audio>";

//here is the new player as of 9/1/22 from Vijay and edited by Dan

$player_text = " 


<head><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=23\" media=\"all\"><body>

<div class=\"holder\">
<div class=\"audio green-audio-player\">
<div class=\"loading\">
<div class=\"spinner\" hidden=\"hidden\"></div>
</div>
<div class=\"play-pause-btn\">  
<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"18\" height=\"24\" viewBox=\"0 0 18 24\">
<path fill=\"#ffffff\" fill-rule=\"evenodd\" d=\"M18 12L0 24V0\" class=\"play-pause-icon\" id=\"playPause\"/>
</svg>
</div>
<div class=\"controls\">
<span class=\"current-time\">0:00</span>
<div class=\"slider\" data-direction=\"horizontal\">
<div class=\"progress\">
<div class=\"pin\" id=\"progress-pin\" data-method=\"rewind\"></div>
</div>
</div>
<span class=\"total-time\" hidden=\"hidden\">0:00</span>
</div>
<div class=\"volume\">
<div class=\"volume-btn\">
<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\">
<path fill=\"#ffffff\" fill-rule=\"evenodd\" d=\"M14.667 0v2.747c3.853 1.146 6.666 4.72 6.666 8.946 0 4.227-2.813 7.787-6.666 8.934v2.76C20 22.173 24 17.4 24 11.693 24 5.987 20 1.213 14.667 0zM18 11.693c0-2.36-1.333-4.386-3.333-5.373v10.707c2-.947 3.333-2.987 3.333-5.334zm-18-4v8h5.333L12 22.36V1.027L5.333 7.693H0z\" id=\"speaker\"/>
</svg>
</div>
<div class=\"volume-controls hidden\">
<div class=\"slider\" data-direction=\"vertical\">
<div class=\"progress\">
<div class=\"pin\" id=\"volume-pin\" data-method=\"changeVolume\"></div>
</div>
</div>
</div>
</div>
<audio id=\"audiomp3\">
<source src=\"uploadmp3/$b\" type=\"audio/mpeg\">
</audio>
<img src=\"images/turtle2.png\" id=\"button\" value=\"PlayBack Rate\" onclick=\"showMore()\" width=\"48\" height=\"48\">
<div id=\"actions\">
<button onclick=\"setPlaySpeed(0.75)\" type=\"button\" class=\"button4\">75%</button>
<button onclick=\"setPlaySpeed(1)\" type=\"button\" class=\"button4\">100%</button>
</div>
</div>
</div>
<script type=\"text/javascript\" src=\"javascript/ispraak_audio_player.js\"></script> 
<br><br>
";


















//also now disable synth since there is audio provided

$synth = "none";

//also disable ispeech since there is audio provided

$i_speech = ""; 

}

if ($synth !== "none")
{

$c2 = addslashes($c);

$synth_text = "<script type=\"text/javascript\">

function myFunction()
		{
		
	speechSynthesis.cancel();	
		
     var uhm = new SpeechSynthesisUtterance();
     uhm.text = '$c2';
     uhm.lang = '$synth';
     uhm.rate = 1.0;
      
     speechSynthesis.speak(uhm);
          
     }  
     
     function myFunction2()
		{
		
			speechSynthesis.cancel();	
		
		
     var ubb = new SpeechSynthesisUtterance();
     ubb.text = '$c2';
     ubb.lang = '$synth';
     ubb.rate = 0.8;
     
     speechSynthesis.speak(ubb);
     } 
     
            function myFunction5()
		{
		
     	document.getElementById(\"alert\").style.display = \"block\";
     }
     

     
  	</script>";
  	
$synth_button = "<img alt=\"iSpraak\" src=\"images\synthx2.gif\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction2()\"><img alt=\"iSpraak\" src=\"images\synthx.gif\" align=\"right\" width=\"30\" id=\"998\" onclick=\"myFunction()\">";

}
else
{

$synth_button = "<script type=\"text/javascript\">
      function myFunction5()
		{
		
     	document.getElementById(\"alert\").style.display = \"block\";
     }
     	</script>";


}


$extra_audio_js = "<script language=\"javascript\" type=\"text/javascript\">
<!--
function popitup(url) {
	newwindow=window.open(url,'name','height=130,width=350');
	if (window.focus) {newwindow.focus()}
	return false;
}

// -->
</script>

";

$extra_audio = "$extra_audio_js <a href=\"tts_call.php?lang=$mylang&mykey=$mykey&btext=$c\" onclick=\"return popitup('tts_call.php?lang=$mylang&mykey=$mykey&btext=$c')\"
	><img src=\"more_audio.png\" align=\"right\" width=\"80\" ></a>";

$extra_audio_safe = "<a href=\"tts_call.php?lang=$mylang&mykey=$mykey&btext=$c\" onclick=\"javascript:void window.open('tts_call.php?lang=$mylang&mykey=$mykey&btext=$c','_blank',
'width=350,height=120,toolbar=0,menubar=0,location=0,status=0,scrollbars=0,resizable=0,left=0,top=0');return false;\"><img src=\"more_audio.png\" align=\"right\" width=\"80\" ></a>";

$extra_audio2 = ""; 

//notes to self

$iframe_text2 = ""; 
$iframe_text2 = "<iframe src=\"premium_tri.php?lang=$mylang&mykey=$mykey&btext=$c_stripped\" align=\"right\" width=\"135\" height=\"50\" scrolling=\"no\" frameBorder=\"0\"></iframe>";

//big change to JUST one page rather than 26 near identical HTML pages
$iframe_text = "<iframe src=\"asr_languages.html\" width=\"600\" height=\"340\" scrolling=\"no\" frameBorder=\"0\"></iframe>";

//make sure a student gets redirected if nothing set for these variables -- it means someone just shared the link from the wrong spot

if ($student_email == "" || $student_name == "")
{
	header('Location: ispraak.php?mykey='.$mykey.'&error=86&mykey2='.$mykey2);
	//echo "<br><br><h3><center> Oops! Something went awry!</h3><br><center>Please try this page: <a href=\"ispraak.php?mykey=$mykey\">REDIRECT</a></center><br><br><center>Please enter your e-mail and name when prompted!<br><br><br><center><img src=\"hal_error.png\" width=50>";
}
else
{



//Check to see if this is an individual activity (default) or part of a set 
$active_set = $_COOKIE["active_set"];


if ($active_set == "true")
{

//Get both mykey arrays from cookies
$array1 = $_COOKIE["array1"];
$array2 = $_COOKIE["array2"];

//Decode JSON array 
$array1 = json_decode($_COOKIE['array1'], true);
$array2 = json_decode($_COOKIE['array2'], true);

//We will take out the first array items in both arrays
$array1 = array_diff($array1, [$mykey]);
$array2 = array_diff($array2, [$mykey2]);

//Reindex array values

$array1 = array_values($array1);
$array2 = array_values($array2);

//Get count of items in both arrays
$a1_count=count($array1);
$a2_count=count($array2);

//We will now get prepare the next link for the next activity

$array_key1=$array1[0];
$array_key2=$array2[0];
$new_array_URL = "ispraak.php?mykey=$array_key1&mykey2=$array_key2&set=YES"; 

//Encode arrays into JSON and then Update cookies

$a1 = json_encode($array1); 
$a2 = json_encode($array2); 
	
//Do not store any cookies AFTER an ECHO output occurs
	
//Store this array as a cookie
	
setcookie("array1", $a1, time()+7200, '/'); 
setcookie("array2", $a2, time()+7200, '/'); 

echo "<div id=\"goForward\" class=\"goForward\" style=\"display:none;\"><a href=\"$new_array_URL\" class=\"cutelink4\"><img src=\"images/arrow_set.png\" class=\"pulse2\"></a></div>";
//$activityset_next = "<div id=\"activityset_next\" style=\"display:none;\"> Go to next! <a href=\"$new_array_URL\" class=\"cutelink3\"><img src=\"images/arrow_set.png\" width=\"25\" style=\"display:inline; padding-left: 10px; vertical-align: middle;\"></a></div>";
$activityset_next = "<div id=\"activityset_next\" style=\"display:none;\"> Go to next! <a href=\"$new_array_URL\" class=\"cutelink3\"><img src=\"images/arrow_set.png\" class=\"pulse\"></a></div>";


if ($a1_count < 1)
{
$activityset_next = "<div id=\"activityset_next\" style=\"display:none;\"> Set is Complete!<img src=\"images/checkmark.png\" class=\"pulse\">  Explore more sets <a href=\"explore.php\" class=\"cutelink3\">here</a>!
</div>";

}

$active_set = "<div class = \"activityset\" id=\"activityset\"> This set has $a1_count activities remaining. $activityset_next</div>";	






}
else
{
$active_set = ""; 
}











//<span style=\"font-size:small\">Push the mic button and practice speaking the text below:</span>

echo "

<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/javascript.js\"></script>
</head>
<body id=\"main_body\" >
	
	<img id=\"top\" src=\"images/top.png\" alt=\"\">
	<div id=\"form_container\">
	<div id=\"headerBar\"></div>
	
		<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"makeit.php\">
					<div class=\"form_description\">
						
					<a href=\"index.html\"><img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"></a> 
			
			$zhtext $synth_button
			<br>

			<span style=\"font-size:medium\">
			<br>$i_speech<br><div class=\"target\">$c</div><br>$extra_audio2</span></p>
			
			$player_text
				
		</div>			
		
		
<div class=\"alert\" id=\"alert\" style=\"display:none\">
  <span class=\"closebtn\" onclick=\"this.parentElement.style.display='none';\">&times;</span> 
  <strong><br><br>$pinyin </strong>
</div>
		
					
			<ul >

$iframe_text
			</ul>
		</form>	
		$ispraak_footer
	</div>
	
	$active_set 
	</body>
</html>
$synth_text
";

}
//end else statement for improper page load

//close your connection to the DB
mysqli_close($msi_connect);

?>