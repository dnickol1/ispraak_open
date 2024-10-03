<?php

/*

This page comes from makeit.php and updates the audio file record with proper filename
after confirming the file did successfully upload. 

The page finishes by providing the instructor with iSpraak links for the activity 

*/

session_start();
//Continues Session from previous PHP page

//Comment the below off to turn off error warnings
error_reporting(0);

//Get database variables
include_once("../../config_ispraak.php");

//get the file name first, and also the unique key for the database update command

$mylink = $_SESSION['mp3link'];
$mykey = $_SESSION['mykey'];
$mykey2 = $_SESSION['mykey2'];


//lets double check that the user-provided file actually made it on the server and update message
$message = ""; 
$filename = 'uploadmp3/'.$mylink; 
if (file_exists($filename))
{
//file confirmed to reside on server
$message = "Your selected audio file will serve as the pronunciation prompt. "; 
}
else
{
//file not found on server
$mylink = "1";
$message = "However, <span style=\"color:red\"> the audio you provided cannot be used</span>, so iSpraak will use a TTS voice instead. ";  
}

//Connect to the database

$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}

//define the query into a string after escaping variables

$mylink = mysqli_real_escape_string($msi_connect, $mylink);
$mykey = mysqli_real_escape_string($msi_connect, $mykey);

//define the query

$query = "UPDATE ispraak SET audiofile='$mylink' WHERE mykey='$mykey'";

//execute the query
mysqli_query($msi_connect, $query);

//check to see if this is an LTI activity and change message accordingly 

$lti_message = "Your activity has been saved & e-mailed to you.</b> You can also copy the links below (right click) in case the automated e-mail does not reach you."; 

$context_id=$_COOKIE['context_id'];
if (strlen($context_id) > 3)
{
	$lti_message = "Great news! <br><br>Your activity has been created in your LMS.</b> Press the REFRESH button in your dashboard to see this activity. ";
}

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
		Your activity has been created and is now ready to be shared. $message Remember to only share the student link (left) and to keep your instructor link (right) private. You can further manage this activity by logging on to the instructor dashboard.<br>
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








//close your connection to the DB
mysqli_close($msi_connect);
?>
