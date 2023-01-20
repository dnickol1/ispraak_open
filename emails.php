<?php

/*

This page allows instructors to view email contact settings and links them
to the unsubscribe page if desired. Emails are sent by default about new
activities and for a daily summary of activities. 

Requires authentication to load (email, token). 

*/

session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//Get function from query string for this page
//Not every call to this page will need all these variables

$email=$_GET['email'];
$ispraak_token=$_GET['token'];
$permission = "denied"; 
$action = $_GET['action'] && 'NA';

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


if ($action == "reset")
{
	$query = "UPDATE ispraak_unsubscribe SET email = 'deleted' WHERE email = '$email'"; 
	$good_update = mysqli_query($msi_connect, $query);
	mysqli_close($msi_connect);
	$newURL = "login.php?email=$email&token=$ispraak_token"; 
	header('Location: '.$newURL);

}

//begin output

echo "$ispraak_header <form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"preferences.php?action=update&email=$email&token=$ispraak_token\">$ispraak_logo

			<a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
	
<br><br><br>"; 

if (mysqli_connect_errno())
{
  	$error_saving_db = "<p style=\"color:red\">Database connection: X</span>";
}
else
{

	$message = "You are currently receiving the Daily Digest and notifications about new activities created with your e-mail address. <br><br>
	
	<li>Unsubscribe from creator e-mails <a href=\"unsubscribe.php?id=$email&action=check&type=NCE\" class=\"cutelink\" target=\"_blank\">(link)</a></li>
	<li>Unsubscribe from the Daily Digest <a href=\"unsubscribe.php?id=$email&action=check&type=NDD\" class=\"cutelink\" target=\"_blank\">(link)</a></li>
	<li>Unsubscribe from all notifications <a href=\"unsubscribe.php?id=$email&action=check\" class=\"cutelink\" target=\"_blank\">(link)</a></li>
	<li>Reset all communication to default  <a href=\"emails.php?email=$email&token=$ispraak_token&action=reset\" class=\"cutelink\" target=\"_blank\">(link)</a></li></li>
	
	";
	//since there is no connection error, prepare input for first query
	$email = mysqli_real_escape_string($msi_connect, $email);
	
	//do email preferences exist for this user 

	$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_unsubscribe where email='$email' AND email_pref_code2 = 'NCE'");
	$num=mysqli_num_rows($myresult);
	$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_unsubscribe where email='$email' AND email_pref_code = 'NDD'");
	$num2=mysqli_num_rows($myresult);
	
	if ($num > 0) { $message = "<p>You are unsubscribed from notifications about newly created activities. 	<li>Unsubscribe also from the Daily Digest <a href=\"unsubscribe.php?id=$email&action=check&type=NDD\" class=\"cutelink\" target=\"_blank\">(link)</a></li>	<li>Reset all communication to default  <a href=\"emails.php?email=$email&token=$ispraak_token&action=reset\" class=\"cutelink\" target=\"_blank\">(link)</a></li></li>"; }
	if ($num2 > 0) { $message = "<p>You are unsubscribed from the Daily Digest. 	<li>Unsubscribe also from creator e-mails <a href=\"unsubscribe.php?id=$email&action=check&type=NCE\" class=\"cutelink\" target=\"_blank\">(link)</a></li>
	<li>Reset all communication to default  <a href=\"emails.php?email=$email&token=$ispraak_token&action=reset\" class=\"cutelink\" target=\"_blank\">(link)</a></li></li>"; }
	if ($num > 0 && $num2 > 0)  { $message = "<p>You are unsubscribed from all iSpraak email notifications. 	<li>Reset all communication to default  <a href=\"emails.php?email=$email&token=$ispraak_token&action=reset\" class=\"cutelink\" target=\"_blank\">(link)</a></li></li>"; }

	echo "$message";

}

//close your connection to the DB
mysqli_close($msi_connect);


echo "

	
			</p>

		</form>	
		$ispraak_footer
	</div>
	</body>
	
</html>";



//above this bracket user is authenticated
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

