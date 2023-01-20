<?php

//Callback, redirect page from AZURE
//Based on tutorial here: https://www.youtube.com/watch?v=LbtwSzTkKo8
//and code from https://github.com/AdnanHussainTurki/microsoft-api-php#readme

session_start();
require "../../vendor/autoload.php";
include_once("../../config_ispraak.php");

use myPHPnotes\Microsoft\Auth;
use myPHPnotes\Microsoft\Handlers\Session;
use myPHPnotes\Microsoft\Models\User;


$mykey=$_GET['mykey'] ?? 'NA';
$scopes_array = ["User.Read"];


//proceed with authentication process if the CODE variable is in query string

if (isset($_GET['code'])) 
{

	//Was getting type errors, sometimes, when sending NULL instead of a string... adding the ?? 'NA' which seems to be working now
	
	$tenant = "common";
	$client_id = $azure_client_id;  
	$client_secret = $azure_client_secret; 
	$callback = "https://ispraak.net/azure_callback.php";
	$scopes = ["User.Read"];
	$auth = new Auth($tenant, $client_id, $client_secret,$callback, $scopes);
	
	
	$tokens = $auth->getToken($_REQUEST['code'], $_REQUEST['state']);
	$accessToken = $tokens->access_token;
	$auth->setAccessToken($accessToken);
	$user = new User;
	$name = $user->data->getDisplayName(); 
	$email = $user->data->getUserPrincipalName();
	
  //do a basic log of this request
	$logrightnow = time();
	$logrightnow = date('m/d/Y H:i:s', $logrightnow);
	$mylogfile = fopen("activity_log.txt", "a") or die("Unable to open log file!");
	$logtxt = "\n $name with an email of $email has Microsoft Authenticated at $logrightnow";
	fwrite($mylogfile, $logtxt);
	fclose($mylogfile);

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
}
else
{
	echo "Microsoft authentication error occurred."; 
}



?>


