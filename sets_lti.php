<?php

/*

This page takes an LTI 1.0 payload from an LMS as POST variables and an Activity
Set as a GET variable. It auto-populates student name and email address and redirects
to the requested set of activities. 

*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

//Get the requested set ID from the query string
$my_set = $_GET['id'] ?? 'NA';

//Get user name and email from LTI post variables
$lis_person_name_full = $_POST['lis_person_name_full'] ?? 'NA';
$lis_person_contact_email_primary = $_POST['lis_person_contact_email_primary'] ?? 'NA';

//Get consumer key as well
$oauth_consumer_key = $_POST['oauth_consumer_key'] ?? 'NA';

//Get name of course as well
$context_title = $_POST['context_title'] ?? 'NA';

//declare session variables for student name and email

$_SESSION['student_name'] = $lis_person_name_full;
$_SESSION['student_email'] = $lis_person_contact_email_primary;
$_SESSION['start_name'] = $lis_person_name_full;
$_SESSION['start_email'] = $lis_person_contact_email_primary;

//styles

echo "<html><head><style>div { margin-bottom: 15px; padding: 4px 12px; } .warning { background-color: #ffffcc; border-left: 6px solid #ffeb3b; } </style></head><body>";

//data validation 

if ($my_set == 'NA' | $lis_person_name_full == 'NA' | $lis_person_contact_email_primary == 'NA')
{

	echo "<h2>Error connecting iSpraak to your Learning Management System.</h2>"; 
	
	if ($my_set != 'NA')
	{
		$redirect_url = "sets_students.php?id=$my_set";
		
		echo "<div class=\"warning\">To use LTI linking, create an external tool assignment with your LMS and provide the following tool URL:<ul> https://www.ispraak.net/sets_lti.php?id=$my_set</ul> ";
		
		echo "Your LMS must be properly configured by your system adminstrator to take advantage of LTI linking.  

		Your LMS administrator can reach out with questions to help@ispraak.net</i></div>"; 
		
		echo "<p>You can proceed to your selected activity set without LTI linking <a href=\"$redirect_url\" target=\"_blank\">here</a>.";
		
		
		
	}
	else
	{
		echo "<div class=\"warning\">The activity set identifier is not indicated in this request. Please check the assignment URL.</div>"; 
	}
	
}
else
{
	//redirect to the actual set in question 
	
	$redirect_url = "sets_students.php?id=$my_set";
	//header("Location: $redirect_url");
	
	echo "<h2>Hello, $lis_person_name_full! </h2>
	
	Your instructor has assigned you iSpraak activity set #$my_set for your course <b>$context_title</b>
	
	<p>
	<div class=\"warning\">Click <a href=\"$redirect_url\" target=\"_blank\">here</a> to continue to iSpraak in a new window.</div>
	<p>
	Your e-mail address ($lis_person_contact_email_primary)  will be shared with iSpraak when you continue. 
	
	";
	
	//var_dump($_POST);

	
}

?>

