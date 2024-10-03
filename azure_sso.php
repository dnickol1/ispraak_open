<?php
//Code for Microsoft Login from here
//https://github.com/AdnanHussainTurki/microsoft-api-php#readme
//The below credentials will expire periodically
//Credentials associated with Azure Account help@ispraak.com
//Go to Entra in Azure, then update Certicicates and Secrets

session_start();
require "../../vendor/autoload.php";
include_once("../../config_ispraak.php");

use myPHPnotes\Microsoft\Auth;
$tenant = "common";
$client_id = $azure_client_id;  
$client_secret = $azure_client_secret; 
$callback = "https://ispraak.net/azure_callback.php";
$scopes = ["User.Read"];
$microsoft = new Auth($tenant, $client_id, $client_secret,$callback, $scopes);
header("location: " . $microsoft->getAuthUrl());

?>

