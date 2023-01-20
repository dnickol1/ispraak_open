<?php

/*

Allows users to unsubscribe from Daily Digest or New Activity emails.

This page does not require authentication but does require query string
variables for email (id), action to perform, and type of unsubscription request. 

*/

//Comment below lines out to stop error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
//Starts or continues session from previous PHP page

//Get config file variables and functions
include_once("../../config_ispraak.php");

//Connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

//Assume there is no error fetching grades
$error = ""; 

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
  	$error = "Unable to connect to database."; 
}

//Get email address from query string 
$id=$_GET['id'];

//Determine if page needs to confirm the unsub or actually execute it
//options are ?action=check ?action=confirm
$action=$_GET['action'];

//Find out which emails are being unsubscribed from
$email_type=$_GET['type'] ?? 'NA';
$etype = "all iSpraak notifications"; 

if ($email_type == "NDD")
{
	$etype = "the Daily Digest"; 
}
if ($email_type == "NCE")
{
	$etype = "notifications about newly created activities"; 
}

//Catch all in case there are other problems with the query string
$error = "Sorry. Uknown error occurred, please check back soon.";

if ($id == "" || $action == "")
{
  	$error = "Zut alors! This page can't load. It is possible that you  have incorrectly entered the URL in the address bar above. Please confirm the link is not broken and reload the page."; 

}
else
{

//Going to check the database, so prepare the email address
$id = mysqli_real_escape_string($msi_connect, $id);

$num = "0";
$mycode = "NDD"; //no daily digest
$mycode2 = "NCE"; //no creator email

if ($email_type == "NDD")
{
	//Has this person already unsubscribed from the Daily Digest 
	$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_unsubscribe where email='$id' AND email_pref_code = 'NDD'");
	$num=mysqli_num_rows($myresult);
	
	$mycode = "NDD"; //no daily digest
	$mycode2 = "NA"; //no creator email
}

if ($email_type == "NCE")
{
	//Has this person already unsubscribed from the Creator Email
	$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_unsubscribe where email='$id' AND email_pref_code2 = 'NCE'");
	$num=mysqli_num_rows($myresult);
	
	$mycode = "NA"; //no daily digest
	$mycode2 = "NCE"; //no creator email
}

if ($num > 0 && $action == "check")  { $error = "It appears you have already unsubscribed the address <strong>$id</strong> from $etype. <br><br>If you want to re-subscribe to this list, please login to your account and update your settings.";}
if ($num == 0 && $action == "check")  
	{ 
		$error = "Please confirm you wish to unsubscribe from $etype for the following email address: <strong>$id</strong><br><br><center>
		<a href=\"unsubscribe.php?id=$id&action=confirm&type=$email_type\" class=\"button5\">Confirm Email Preference</a></center>";
	}
if ($num == 0 && $action == "confirm")  
	{ 
		$error = "You have successfully unsubscribed the address: $id";
		$rightnow = time();
		$query = "INSERT INTO ispraak_unsubscribe VALUES ('$id', '$mycode', '$mycode2', '$rightnow','')";
		//execute the query and determine if it was a good insert
		$good_insert = mysqli_query($msi_connect, $query);
		if (!$good_insert)	{ $error = "Oops. Sorry unable to update your email preferences right now.";}
	}
}

echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br><p>$error";

echo "<br><br></div></p></form>$ispraak_footer</div></body></html>";
			
//close your connection to the DB
mysqli_close($msi_connect);

?>