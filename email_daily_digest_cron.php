<?php
//This page is automatically activated once a day and sends in the respective daily digest information to the users. 

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

$my_string = $_GET["cronkey"];

if ($my_string != $ispraak_cronkey)
{
	//cronkey variable saved in configuration file
	echo "Incorrect cron session key found in URL. Unable to send out Daily Digest";
	
}
else
{

	//proceed sending out daily digest to subscribed creaters

	//connect to the database
	$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

	if (mysqli_connect_errno())
	{
		echo "Unable to connect to the database. Please try again later.";
	}

	//Going to identify activities with submissions in past 24 hours

	$timenow = time();
	$one_day_ago = ($timenow - 86400); 

	//figure out which teachers need to be e-mailed - use DISTINCT to avoid emailing same teacher twice

	$myresult = mysqli_query($msi_connect, "SELECT DISTINCT teacher_email FROM ispraak_grades WHERE timestamp > $one_day_ago");
	$row = mysqli_fetch_array($myresult);
	$num=mysqli_num_rows($myresult);

	//loop through each e-mail to determine what activity IDs have new content

	$p = 0;
	while ($p < $num) 
	{
     	//find the teacher email address
     	$tname=mysqli_result($myresult,$p,"teacher_email");
          
     	$start_body = "There have been some new student submissions on your iSpraak account. <a href=\"$domain_name/login.php\">Login</a> to your account to review the details! <br><ul>";
     
     	$student_body = "<h3>Recent iSpraak Activity for $tname</h3>$start_body";
     
     	$endbody = "</ul>For technical assistance, please send an e-mail to help@ispraak.com. This message has been sent from an address that is not monitored. If you don't want to receive the daily digest, you can <a href=\"$domain_name/unsubscribe.php?id=$tname&action=check&type=NDD\">unsubscribe</a>.";

     	$result2 = mysqli_query($msi_connect, "SELECT DISTINCT activity_id FROM ispraak_grades WHERE timestamp > $one_day_ago AND teacher_email = '$tname'");
     	$row2 = mysqli_fetch_array($result2);
     	$num2=mysqli_num_rows($result2);
     
     	$middle_body = " "; 
     
    	$q = 0;
     
     	while ($q < $num2)
     	{
     
			$aids=mysqli_result($result2,$q,"activity_id");     
     	 	$result2x = mysqli_query($msi_connect, "SELECT blocktext FROM ispraak WHERE mykey = '$aids'");
     	    $btext=mysqli_result($result2x,0,"blocktext");
			$rest=mb_strcut($btext, 0, 42, "UTF-8");
			$rest = $rest . ""; 
			$list_number = $q+1; 
     	 	$middle_body = $middle_body."<li>$rest</li>";     		 
     		$q++; 
   
     	} 
     
     $full_email_body = $student_body.$middle_body.$endbody;
     
     echo "<br>$full_email_body"; 
     
   
    //sensitive email information from config variables
    
	$hostz = $mail_host;
	$portz = $mail_port; 
	$usernamez = $mail_username; 
	$passwordz = $mail_password; 
	
	//non-sensitive email variables below
	
    $from = "iSpraak <ispraak.bot@ispraak.com>";
	$contentz = "text/html; charset=utf-8";
	$mimez = "1.0";
	$reply_addressz = "no_reply@ispraak.com";
	$email = $tname;
	$email_cc = "slulanguages@gmail.com";
	$subject = "iSpraak Daily Report for $tname ";

	$headers = array ('From' => $from,
 	 'To' => $email,
  	'Subject' => $subject,
  	'Reply-To' => $reply_addressz,
  	'MIME-Version' => $mimez,
  	'Content-type' => $contentz,
  	'Date' => date('r', time()));
  	
    $smtp = Mail::factory('smtp',
     array ('host' => $hostz,
    'port' => $portz,
    'auth' => true,
    'username' => $usernamez,
    'password' => $passwordz));
    
    //check to see if this user has opted out of the daily digest emails
        
    $tname2 = mysqli_real_escape_string($msi_connect, $tname);
    $myresultw = mysqli_query($msi_connect, "SELECT * FROM ispraak_unsubscribe WHERE email = '$tname2' AND email_pref_code = 'NDD'");
	$numw=mysqli_num_rows($myresultw);

	if ($numw > 0)
	{
		echo "<br>No e-mail for you $tname2, because you opted out!<br>";
	} 
	else
	{
		$mailz = $smtp->send($email, $headers, $full_email_body);
		$mailz = $smtp->send($email_cc, $headers, $full_email_body);
		
		echo "<br>Emails sent to $email and $email_cc";
		
	}

         
$p++;

}

echo "<br><br>That's it!<br>"; 

//close your connection to the DB
mysqli_close($msi_connect);

//end the cron key
}

?>