<?php

/*

We want this page to receive the LTI payload from Moodle/Canvas/etc and to declare session
variables that can be used for other pages. 

*/

session_start();


//Make sure we include the Vendor Directory
require_once '../../../vendor/autoload.php';

//Import the LTI vendor library
use Packback\Lti1p3; 

//Get database variables: this path is  confirmed 
include_once("../../../config_ispraak_dev.php");
include_once("../../../database.php");

//Variables we want to define from LTI tool
$lis_person_name_full = "undefined"; 
$lis_person_contact_email_primary = "undefined"; 
$context_title = "undefined"; 
$resource_link_id = "undefined"; 
$roles = "undefined"; 

LTI\LTI_OIDC_Login::new(new Example_Database())
    ->do_oidc_login_redirect(TOOL_HOST . "../../../makeit.php")
    ->do_redirect();

//Take the POST payload and define variables
foreach($_POST as $key => $value) 
{
  
  //show all data in post request
  print "$key=$value\n";
  
  //set variables based on post request
  if ($key === "lis_person_name_full") { $lis_person_name_full = "$value";	}
  if ($key === "lis_person_contact_email_primary") { $lis_person_contact_email_primary = "$value";	}
  if ($key === "context_title") { $context_title = "$value";	}
  if ($key === "context_id") { $resource_link_id = "$context_id";	}
  if ($key === "resource_link_id") { $resource_link_id = "$value";	}
  if ($key === "roles") { $roles = "$value";	}
  
} 

//Create session variables for each LTI item

$_SESSION['lis_person_name_full'] = $lis_person_name_full;
$_SESSION['lis_person_contact_email_primary'] = $lis_person_contact_email_primary;
$_SESSION['context_title'] = $context_title;
$_SESSION['resource_link_id'] = $resource_link_id;
$_SESSION['roles'] = $roles;

//Now redirect to page based on role 

if ($roles === "instructor") { 
//go to instructor page 
}
else
{
//go to student page 
}

?>