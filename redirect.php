<?php

//This page allows an iSpraak user to connect with Google Credentials

require_once '../../vendor/autoload.php';

//Get the configuration file functions and variables

include_once("../../config_ispraak.php");

$copyright_year = date("Y");
 
// init configuration - put these variables in ispraak config file
$clientID = '739914882452-spgvoc0r725sj66v3kfvei6j68etnice.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'https://www.ispraak.net/redirect.php';
  
// create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");
 
// authenticate code from Google OAuth Flow
if (isset($_GET['code'])) 
{
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  $client->setAccessToken($token['access_token']);
  
  // get profile info
  $google_oauth = new Google_Service_Oauth2($client);
  $google_account_info = $google_oauth->userinfo->get();
  $email =  $google_account_info->email;
  $name =  $google_account_info->name;
    
  //do a basic log of this request
	$logrightnow = time();
	$logrightnow = date('m/d/Y H:i:s', $logrightnow);
	$mylogfile = fopen("activity_log.txt", "a") or die("Unable to open log file!");
	$logtxt = "\n $name with an email of $email has Google Authenticated at $logrightnow";
	fwrite($mylogfile, $logtxt);
	fclose($mylogfile);

  // alert the user they have been authenticated (commented out to allow the php redirect header to work below)
  	
  //connect to the database
	$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

	if (mysqli_connect_errno())
	{
  	   echo "Failed to connect to MySQL because: " . mysqli_connect_error();
	}
	else
	{
	  //since there is no connection error, prepare the email adress and other variables
		$email = mysqli_real_escape_string($msi_connect, $email);
		$ispraak_time = time();
		$ispraak_token=substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1).substr(md5(time()),1);
		$visitor_ip = getIP();
	
	  //make a cookie from hex version of email address
		$id_cookie = bin2hex($email); 
		setcookie("id_cookie", $id_cookie, time()+7200, '/'); 
	
	  //define the query
		$query = "INSERT INTO ispraak_auth VALUES ('$email', '$ispraak_token', '$visitor_ip', '$ispraak_time','')";

	  //run the query
		$good_insert = mysqli_query($msi_connect, $query);
	
		if (!$good_insert)
		{
			echo "Failed to proceed with authentication...";
  		}
  		else
  		{
  			header("Location: $domain_name/login.php?token=$ispraak_token&email=$email");
  		}
  		
  		//end connection error if-else statement
  	}
  	
  	//close your connection to the DB
	mysqli_close($msi_connect);

  	
		
} 
else
{
	//redirect user to the Google Login page.... 

	$redirect_variable = $client->createAuthUrl();
	
	header("Location: $redirect_variable");

}


?>

