<?php

/*

Takes form variables from index.html, validates them, and inserts a new activity into the database. 
Users may then be redirected to upload a file, record a file, or simply be provided with a student 
and instructor link to access the activity. 

*/

//Continues Session from previous PHP page
session_start();

//Comment below lines out to stop error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Get database and configuration variables, and custom functions
include_once("../../config_ispraak.php");

//PHP Pear Packages Needed
ini_set("include_path", '/home2/dnickol1/php:' . ini_get("include_path") );

//Do not use default PHP mail function
require_once "Mail.php";

//Detect language for English
require_once "Text/LanguageDetect.php";


//Look up IP and USER Agent for Logging and SPAM control
$visitor_ip = getIP();
$user_agent = $_SERVER['HTTP_USER_AGENT'];
 
//grab variables from Form that was submitted
$email=$_POST['element_1'];
$language=$_POST['element_3'];
$audiofile=$_POST['element_4'];
$blocktext=$_POST['element_2'];
$honey_pot=$_POST['user_zip_kode'];
//$btext=$_COOKIE['element_2'];

//make all variables into session variables for use on other pages
$_SESSION['email'] = $email;
$_SESSION['language'] = $language;
$_SESSION['blocktext'] = $blocktext;

//do a basic log of this request for troubleshooting and spam control
$logrightnow = time();
$logrightnow = date('m/d/Y H:i:s', $logrightnow);
$mylogfile = fopen("activity_log.txt", "a") or die("Unable to open log file!");
$logtxt = "\n $email ($language)($audiofile) honey: $honey_pot IP: $visitor_ip at $logrightnow\n $blocktext\n $user_agent \n";
fwrite($mylogfile, $logtxt);
fclose($mylogfile);

//might be an LTI entry to this page...
//this was to check for an LTI call to this page
/*
$lti_running = "no";
$lti_message = " and e-mailed to you. You can also copy the links below (right click) in case the automated e-mail does not reach you or if you have unsubscribed from the creator e-mails."; 
$context_id=$_GET['cid'];
if (strlen($context_id) > 3)
{
	$lti_message = " and is now available in your LMS. Press the REFRESH button in your dashboard to see this activity. ";
	$lti_running = "yes";
}
*/

//create an error message variables, by default no error and everything assumed good 
$error_saving_db = "<p style=\"color:green\">Database connection: ✓</span>";
$vcode="good";
$validity=""; 

//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	$error_saving_db = "<p style=\"color:red\">Database connection: X</span>";
  	$vcode = "bad";
  	//echo "Unable to connect to the database. Please try again later.";
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}
else
{
//since there is no connection error, sanitize all the user input
$email = mysqli_real_escape_string($msi_connect, $email);
$language = mysqli_real_escape_string($msi_connect, $language);
$audiofile = mysqli_real_escape_string($msi_connect, $audiofile);
$blocktext = mysqli_real_escape_string($msi_connect, $blocktext);
}

//Dont need to check for registered accounts, all will be free

//First check to see if this request is coming from a valid account
//with a valid e-mail address
/*
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_accounts");
//$num=mysql_numrows($myresult);
$num = $myresult->num_rows;
$validity="<br><br><p style=\"color:red\">You are using the free version of iSpraak with an unregistered account ($email). Some features will be disabled, such as premium text-to-speech services. Please consider registering to help financially support this project! </p>";
$vcode="good";
$vcode2="unregistered";
$warnyou="";
$i=0;
while ($i < $num) 
{
$required_etext=mysqli_result($myresult,$i,"required_etext");
$institution=mysqli_result($myresult,$i,"institution");
$expiry=mysqli_result($myresult,$i,"expiry");
$readable_date = date('m/d/Y', $expiry);
if (strpos($email,$required_etext) !== false) 
{
$validity="<br><br>Registered Account: $institution <br>Licensed until: $readable_date";
$vcode="good";
$vcode2="good";
//If e-mail is valid, should also check on account expiry date
$timenow = time();
if ($timenow > $expiry)
	{
		$warnyou="<br><br><p style=\"color:red\">Please renew your account or complete your registration for $institution!</p>";
		$vcode="bad";
	}
}
$i++;
}
//Query how many times a demo e-mail address has been used
if ($vcode2 == "unregistered")
{
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak WHERE email='$email' ORDER BY mykey DESC");
//$num=mysql_numrows($myresult);
$num = $myresult->num_rows;
	if ($num > 100)
	{
	$warnyou="<p style=\"color:red\">You have exceeded your trial for iSpraak. Please register!</p>";
	$vcode="bad";
	}
}
*/

//create an ID pair for this activity
$mykey = time();
$mykey2=substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1).substr(md5(time()),1);


//make session variables to be used by MP3 uploader
$_SESSION['mykey'] = $mykey;
$_SESSION['mykey2'] = $mykey2;

//check for a valid e-mail address string
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Double check that e-mail address!</p>";
}

//check for empty variables
if ($email == "")
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Double check that e-mail address!</p>";
}


//check if text is actually English 
if ($language == "en") 
{
	//very short texts cannot be reliably parsed to determine language, so check for length
	$detect_length = strlen($blocktext);
	if ($detect_length > 30)
	{
		$match = detectLanguage($blocktext,$language);
		if($match == false) 
		{
		$vcode = "bad";
		$warnyou = "<br><p style=\"color:red\">Oops. Language mismatch detected.<br><br> Please check your spelling, language choice, or reformulate your prompt.</p>";
		}
	}
}

//check for empty variables
if ($blocktext == "")
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Oops! You did not include any text to save.</p>";
}

//anti-spam measures here, based on history of logs
if (strpos($email,'@') == false) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Double check that e-mail address!</p>";
}

if (strpos($email,'@ispraak.com') == true) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Double check that e-mail address!</p>";
}

if (strpos($email,'@ispraak.net') == true) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Double check that e-mail address!</p>";
}

if (strpos($blocktext,'http') == true) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Unable to create activity. Text should consist of words only.</p>";
}

if (strpos($blocktext,'www') == true) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Unable to create activity. Text should consist of words only.</p>";
}

if (strpos($blocktext,'.com') == true) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Unable to create activity. Text should consist of words only.</p>";
}

if (strpos($blocktext,'.net') == true) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Unable to create activity. Text should consist of words only.</p>";
}

if (strpos($blocktext,'.org') == true) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Unable to create activity. Text should consist of words only.</p>";
}

if (strpos($blocktext,'bit.ly') == true) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Unable to create activity. Text should consist of words only.</p>";
}



if (strpos($blocktext,'@') == true) 
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Unable to create activity. Text should consist of words only.</p>";
}


$hugeness = strlen($blocktext);
if ($hugeness > 500)
{
	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Unable to create activity. Please try a shorter text.</p>";
}


if($blocktext != strip_tags($blocktext))
{
  // this contains HTML  
  	$vcode = "bad";
	$warnyou = "<br><p style=\"color:red\">Unable to create activity. HTML tags detected.</p>";

}






//honey pot entries from spammers look the same, range of numbers between 100,000 and 200,000
//could change this to just check if ANYTHING is sent as a variable here
if (is_numeric("$honey_pot")) 
{ 
	if ($honey_pot > 100000)
	{
		$vcode = "bad";
		$warnyou = "<br><p style=\"color:red\">Unable to create activity. Please disable autofill.</p>";
	}
 
}

//end anti-spam measures... more can be added as logs indicate new problems

if ($vcode == "good")
{

//add both mykey (time) and mykey2 (random) and an auto increase column

//define the query
$query = "INSERT INTO ispraak VALUES ('$email', '$language', '$audiofile', '$blocktext','$mykey', '$mykey2','')";

//execute the query
//determine if it was a good insert
$good_insert = mysqli_query($msi_connect, $query);

//new monster IF statement to avoid duplicate mykey issue
//or any other INSERT problem

if (!$good_insert)
{
//new activity was not inserted

$warnyou = "<br><p style=\"color:red\">Record unable to be updated right now.</p>";
	

echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script>
</head>
<body id=\"main_body\" >
	
	<img id=\"top\" src=\"images/top.png\" alt=\"\">
	<div id=\"form_container\">
	<div id=\"headerBar\"></div>
	
		<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"makeit.php\">
					<div class=\"form_description\">
					
					
					<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
		
			<br><br><br><center>Zut alors! We're having a problem saving your activity right now!<br><br>$error_saving_db $warnyou<br><br><br>
<br>		</div>		
			</p>
		</form>	
		<div id=\"footer\">
			© D. Nickolai
		</div>
	</div>
	</body>
</html>";



}
else
{
//new activity was inserted


//if this request came from the LTI, you need to update that database as well
//for development purposes, putting no for now
$lti_running = "no";

if ($lti_running === "yes")
{
	$role = "instructor"; 
	$misc999 = "999"; 
	$query2 = "INSERT INTO ispraak_lti VALUES ('$context_id', '$mykey', '$email', '$role','$misc999','$misc999','$mykey','')";
	mysqli_query($msi_connect, $query2);
}
else
{

//2018, LTI NOT running, so send an e-mail

//The email delivery is what will allow us to limit unregistered or expired users
//from finding out their activity code
//Let's define strings based on the vCodes saved above
//Can also include the warn-you variable text in the e-mail


$helpu = "<br><br>For other help and activity creation guidelines, check out our
 help page <a href=\"$domain_name/help.html\">here.</a> If you don't want to receive these activity creation e-mails, you can <a href=\"$domain_name/unsubscribe.php?id=$email&action=check&type=NCE\">unsubscribe</a>.";

$blocktext_strip = stripcslashes($blocktext); 

$to5 = "slulanguages@gmail.com";
$subject = "iSpraak Activity Created";
$from = "iSpraak <ispraak.bot@ispraak.com>";
$student_body = "<table border = 0 width = 500><tr><td><h3>Your iSpraak Links</h3>New activity has been created for $email:<br><br><b>$blocktext_strip</b><br><br>Shareable student link: <a href=\"$domain_name/ispraak.php?mykey=$mykey&mykey2=$mykey2\">HERE</a><br>Private instructor link: <a href=\"$domain_name/grades.php?mykey=$mykey&mykey2=$mykey2\">HERE</a></b><br><br>For technical assistance, please send an e-mail to help@ispraak.com. This message has been sent from an address that is not monitored. $helpu </td></tr>";
$student_body2 = "<table border = 0 width = 500><tr><td><h3>iSpraak Links for $email</h3><br>New activity #$mykey has been created.<br><br>Student link: <a href=\"$domain_name/ispraak.php?mykey=$mykey&$mykey2\">HERE</a><br>Instructor link: <a href=\"$domain_name/grades.php?mykey=$mykey&dou=courr&mykey2=$mykey2\">HERE</a></b><br><br>For technical assistance, please send an e-mail to help@ispraak.com. This message has been sent from an address that is not monitored. $helpu </td></tr>";

//new variables for august 2018
//renamed these variables in the config file, be sure to chech them out
/*
$hostz = "ssl://mail.ispraak.com";
$portz = "465";
$usernamez = "ispraak.bot@ispraak.com";
$passwordz = "ksfkjMMM345dkkkL";
$contentz = "text/html; charset=utf-8";
$mimez = "1.0";
$reply_addressz = "no_reply@ispraak.com";
$mail_host = "ssl://mail.ispraak.net";
$mail_port = "465";
$mail_username = "ispraak.bot@ispraak.net";
$mail_password = "ZZZo5mFEEMKwjNcuRnTolKm";
$mail_content = "text/html; charset=utf-8";
$mail_mime = "1.0";
$mail_reply_address = "no_reply@ispraak.net";
*/

$headers = array ('From' => $from,
  'To' => $email,
  'Subject' => $subject,
  'Reply-To' => $mail_reply_address,
  'MIME-Version' => $mail_mime,
  'Content-type' => $mail_content,
  'Date' => date('r', time())
  
  );
$smtp = Mail::factory('smtp',
  array ('host' => $mail_host,
    'port' => $mail_port,
    'auth' => true,
    'username' => $mail_username,
    'password' => $mail_password));

//$mailz = $smtp->send($email, $headers, $student_body);
//old functions that often go straight to junk folder 
//mail($email, $subject, $student_body, "From: $from\nContent-Type: text/html; charset=iso-8859-1");

//enough people have asked to stop getting these for every assignment, so.... 
//has this person opted out of e-mail communication, let's find out
    
    $tname2 = mysqli_real_escape_string($msi_connect, $email);
    $myresultw = mysqli_query($msi_connect, "SELECT * FROM ispraak_unsubscribe WHERE email = '$tname2' AND email_pref_code2 = 'NCE'");
	//$numw=mysql_numrows($myresultw);
	
	$numw=mysqli_num_rows($myresultw);
	
	$corrected_text = ""; 

	if ($numw > 0)
	{
		$corrected_text = "<br>Note: $tname2 has unsubscribed from the creator's e-mail.<br>";
	} 
	else
	{
		//below line sends a copy to our generic gmail
		//mail($to5, $subject, $student_body2, "From: $from\nContent-Type: text/html; charset=iso-8859-1");
		$mailz = $smtp->send($email, $headers, $student_body);
		//echo "debuggin - $mailz";
	
	}

//good as of august 2020
//mail($to5, $subject, $student_body2, "From: $from\nContent-Type: text/html; charset=iso-8859-1");

//end non-LTI e-mail generator
//no sense in emailing a bunch when LTI system is keeping track 
}


//in this condition, the user will rely on TTS pronunciation model 

if ($audiofile == "1")
{

echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script>
</head>
<body id=\"main_body\" >
	
	<img id=\"top\" src=\"images/top.png\" alt=\"\">
	<div id=\"form_container\">
	<div id=\"headerBar\"></div>
	
		<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"makeit.php\">
					<div class=\"form_description\">
					
					<a href=\"index.html\">
					<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"></a> 
	<br><br><br></div>
		Your activity has been created and is now ready to be shared. Remember to only share the student link (left) and to keep your instructor link (right) private. You can further manage this activity by logging on to the instructor dashboard.<br>
								<br><br>
								<center>
								
<div class=\"tbl\"><div class=\"col2\"><div class=\"cell\">								
								
			<a target=\"_blank\" class=\"cutelink\" href=\"$domain_name/ispraak.php?mykey=$mykey&mykey2=$mykey2\">Review Activity (Student Link)</a><br>
			<br><br></div></div>
			
			<div class=\"col2\"><div class=\"cell\">		
			
			<a target=\"_blank\" class=\"cutelink\" href=\"$domain_name/grades.php?mykey=$mykey&mykey2=$mykey2\">Check Grades (Instructor Link)</a> <br>
			
			<br><br>			
			
			</div></div></div>
			
			</p>
		</form>	
		$ispraak_footer
	</div>
	</body>
</html>";

}

//in this condition, the user wants to upload an existing MP3 file to serve as pronunciation model

if ($audiofile == "2")
{

echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?ver=12\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script>
<script type=\"text/javascript\" src=\"javascript/mp3.js\"></script>
</head>
<body id=\"main_body\" >
	
	<img id=\"top\" src=\"images/top.png\" alt=\"\">
	<div id=\"form_container\">
	<div id=\"headerBar\"></div>
	
		<form id=\"form_1007732\" name =\"uploadmp3\" class=\"ispraak_form\" enctype=\"multipart/form-data\" method=\"post\" action=\"mp3uploader.php\">
					<div class=\"form_description\">
					
					<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
	<br><br><br>       <img src = \"images/gears.gif\" id=\"upload\" width=\"100\" align=\"right\"> 
       
					
			<br><div id=\"please\">Please select an MP3 file to upload from your computer. File cannot be greater than 2 MB.</div>
			
			<div id=\"please2\">Thank you for selecting a file. Click below to contine.</div>
			
			
			<br><br></p>
		</div>						
			<ul >
<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"4999999\"/>
<input name=\"file\" accept=\".mp3\" id=\"file\" type=\"file\"/> 

<input type=\"hidden\" name=\"form_id\" value=\"329912\" /><br><br>
			
                <input type=\"submit\" class=\"button4\" id=\"submit1\" name=\"submit\" value=\"Upload File\"/>

				<center><button type=\"button\" class=\"button4\" id=\"btnContinuemp3\" onclick=\"location.href='edit.php'\">Continue with this audio</button></center>

				<div id=progress-bar>

				<progress value = \"0\" max=\"100\" ></progress>

				</div>

				<p class=\"error\"></p>
				<p class=\"success\"></p>

                </form>
                </ul>
		</form>	
		<br>
		<div id=\"footer\">
			© D. Nickolai
		</div>
	</div>
	<style>

	#progress-bar{
		height: 50px;
		width: 260px;

	}



	</style>
	<script>
	
	document.getElementById('upload').style.display=\"none\";	
	document.getElementById('progress-bar').style.display=\"none\";	
	document.getElementById('btnContinuemp3').style.display=\"none\";	
	document.getElementById('please2').style.display=\"none\";	
	

	document.forms[\"form_1007732\"].onsubmit = function(e) {
		document.getElementById('progress-bar').style.display=\"none\";	
		e.preventDefault();

		let error = document.querySelector(\".error\");
		let success = document.querySelector(\".success\");

		let file = this.file.files[0];
		error.innerHTML=\"\";

		if(!file){
			error.innerHTML=\"Please select a file\";
			return false;
		}

		let formdata = new FormData();
		formdata.append(\"file\", file);

		let http = new XMLHttpRequest();
		http.upload.addEventListener(\"progress\", function(event){

			let percent = (event.loaded / event.total) * 100;
			document.querySelector(\"progress\").value = Math.round(percent);
			document.getElementById('upload').style.display=\"inline\";	
	


		});

		http.addEventListener(\"load\" , function(){

			if(this.readyState ==4 && this.status==200){
				success.innerHTML= \" \";
					document.getElementById('upload').style.display=\"none\";	
					document.getElementById('please').style.display=\"none\";						
					document.getElementById('btnContinuemp3').style.display=\"inline\";
					document.getElementById('please2').style.display=\"inline\";
		
	
			}
		});

		http.open(\"POST\", \"mp3uploader.php\", true);
		http.send(formdata);

		document.getElementById('submit1').style.display=\"none\";	
		document.getElementById('file').style.display=\"none\";	
	
		


	}

	</script>
	</body>
</html>";

}

//redirect if user wants to do an in-browser recording

if($audiofile=="3")
{
	header('Location: audio_recorder_mp3.php');

}

//all the above brackets is for successful insert 
//into mysql db
}


}
else
{
//vcode is bad so alert user that activity cannot be saved now 

echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script>
</head>
<body id=\"main_body\" >
	
	<img id=\"top\" src=\"images/top.png\" alt=\"\">
	<div id=\"form_container\">
	<div id=\"headerBar\"></div>
	
		<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"makeit.php\">
					<div class=\"form_description\">
					
					
					<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
	
		
				
					
			<br><br><br><center>Zut alors! We're having a tiny problem saving your activity. <br><br>$error_saving_db $validity $warnyou <br><br><br>
<br>		</div>		
			</p>
		</form>	
		$ispraak_footer
	</div>
	</body>
</html>";


}





//close your connection to the DB
mysqli_close($msi_connect);
?>