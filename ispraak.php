<?php

/*

This is the start page for locating and presenting activities to students. 

Requires minimally $mykey and $meykey2 to locate and serve activity. 

Users(students) are first asked to fill in their name and email address to log in 
and start working on the activities. An anonymous bypass is also available to the right of the login. 

*/

session_start();

//Get database variables from configuration file
include_once("../../config_ispraak.php");

//Get mykey from query string & declare session variable
$mykey=$_GET['mykey'];
$_SESSION['mykey']=$mykey;
$mykey2=$_GET['mykey2'];
$_SESSION['mykey2']=$mykey2;

//Determine if this is part of a set
$set_redirect=$_GET['set'] ?? 'NA';


//is there an error here, such as no username or email? 
$error=$_GET['error'];

if ($error == 86)
{
	$error = "<i><b><div class=\"target\">Please enter name and e-mail address to proceed!</div></b></i><br><br>";
}
else
{
	$error = "";
}

//preload name and email from a previous session if you can

if (isset($_SESSION['start_name']))
{
	$start_name = $_SESSION['start_name'];
	$start_email = $_SESSION['start_email'];

	if ($set_redirect != "NA")
	{
		//we have the student's name and email in a session so we don't need to ask again
		//review.php?mykey=$mykey&mykey2=$mykey2&mykey3=anonymous
		$redirect_url = "review.php?mykey=$mykey&mykey2=$mykey2&mykey3=set"; 
		header('Location:'.$redirect_url);
	}

}
else
{
	$start_name = "";
	$start_email = "";
}

//connect to the DB to verify this is a real activity

$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}
else
{
//no database error

$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey='$mykey' AND mykey2='$mykey2'");
$j = 0;
$temail=mysqli_result($myresult,$j,"email");
$student_name_field = "Your Name: "; 
$student_name_field2 = "By sharing your name and email, your instructor can find and see your submission! <br><br>If you'd rather stay anonymous, just click <a href=\"review.php?mykey=$mykey&mykey2=$mykey2&mykey3=anonymous\" class=\"cutelink\">here!</a>"; 
$student_name_field3 = "maxlength=\"255\""; 
$field_status = "";
$student_name_field4 = "Your Email: ";
	
//display form in the event there is no known activity associated with this link

if ($temail == "")
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
					
					
					<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
	
		
				
					
			<br><br><br><center>Zut alors! We're having a tiny problem finding your activity. <br><br>Please confirm you did not click on a broken or incomplete link!<br><br><br>
<br>		</div>		
			</p>

		</form>	
		$ispraak_footer
	</div>
</body>
</html>";

}
else
{	

//Display successful form
//Check to see if this is an individual activity (default) or part of a set 
$active_set = $_COOKIE["active_set"];

//Change start button as needed
$next_text = "Start Activity";

if ($active_set == "true")
{

//Get both mykey arrays from cookies
$array1 = $_COOKIE["array1"];
$array2 = $_COOKIE["array2"];

//Decode JSON array 
$array1 = json_decode($_COOKIE['array1'], true);
$array2 = json_decode($_COOKIE['array2'], true);

//Get count of items in both arrays
$a1_count=count($array1);
$a2_count=count($array2);

$active_set = "<div class = \"activityset\" id=\"activityset\"> This set has $a1_count activities remaining.</div>";	

$next_text = "Start Next Activity";


if ($a1_count < 1)
{
$active_set = "";
$active_set_cookie = "false";
setcookie("active_set", $active_set_cookie, time()+7200, '/'); 
	
}


}
else
{
$active_set = ""; 
}


$temail_hide = hide_email($temail);
 

echo "$ispraak_header
	
		<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"review.php?mykey=$mykey&mykey2=$mykey2\">
			
					<div class=\"form_description\">
		$ispraak_logo
			Welcome to iSpraak! Your teacher <b>($temail_hide)</b> has shared this pronunciation activity with you. Please enter your info below and let's practice speaking!<br><br>
		</div>		$error				
			<ul >
			
					<li id=\"li_1\" >
		<label class=\"description\" for=\"student_name\">$student_name_field</label>
		<div>
			<input id=\"student_name\" name=\"student_name\" class=\"element text medium\" type=\"text\" $student_name_field3 value=\"$start_name\"/> 
		</div><p class=\"guidelines_on\" id=\"guide_1\">$student_name_field2</p> 
		</li>		
		
					<li id=\"li_2\" >
		<label class=\"description\" for=\"student_email\">$student_name_field4 </label>
		<div>
			<input id=\"student_email\" name=\"student_email\" $field_status class=\"element text medium\" type=\"text\" maxlength=\"255\" value=\"$start_email\"/> 
		</div><p class=\"guidelines\" id=\"guide_1\"></p> 
		</li>		
		
					<li class=\"buttons\">
			    <input type=\"hidden\" name=\"form_id\" value=\"1007732\" />
			    
				<input id=\"saveForm\" class=\"button5\" type=\"submit\" name=\"submit\" value=\"$next_text\" />
		</li>
			</ul>
			
			
			<div class=\"alert\" id=\"alert\" style=\"display:none\">
  <span class=\"closebtn\" onclick=\"this.parentElement.style.display='none';\">&times;</span> 
  <strong>Warning: Google Chrome is strongly recommended for iSpraak voice activities! You may experience errors with this browser if you continue.</strong>
</div>	
		</form>	
		$ispraak_footer
	</div>

$active_set 

	</body>
	
<script>
    if (!('webkitSpeechRecognition' in window)) 
	{
	document.getElementById(\"alert\").style.display = \"block\";
	}
	
	notChrome();
	
</script>	
	
	
</html>";

//teacher email identified if statement
}

//end no DB error IF statement
}

//close your connection to the DB
mysqli_close($msi_connect);

?>

