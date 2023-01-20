<?php

/* 

This page displays a login screen and users can be authenticated via an e-mailed token, 
a Google Account, or a Microsoft account. 

Token authentication begins with a database check for the e-mail address, expiry time,
and token string. Assuming these are valid, furhter authentication is dependent on
either IP address (in database) verification or cookie (in browser) verification. 

When the user is authenticated, a dashboard is displayed with options for the user
to see grades, stats, activity links, and other preferences. 

*/

session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//PHP Pear Packages Needed
ini_set("include_path", '/home2/dnickol1/php:' . ini_get("include_path") );

//Do not use default PHP mail function
require_once "Mail.php";

//Get mykey from query string & declare session variable
$mykey=$_GET['mykey'];
$_SESSION['mykey']=$mykey;
$mykey2=$_GET['mykey2'];
$_SESSION['mykey2']=$mykey2;

//Get the authentication token from query string if there is one
$ispraak_token=$_GET['token'];

//Get the authentication email from query string if there is one
$auth_email=$_GET['email'];

//Does the query string ask to be e-mailed a new token?
$action=$_GET['action'];

//Put in a login button on forms where it is needed
$login_button = "<center><a href=\"login.php?action=reset\" class=\"button5\">Login</a></center> "; 

if ($action == "email")
{

	//assume a successful attempt to login occurs and set the variable for the message	
	$login_message = "Your authentication link has been e-mailed to you!<br><br>Please check your e-mail to continue."; 
	$login = "good";

	//collect the e-mail from the submission
	$email=$_POST['user_email'];
	
	//validate email
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
	{
	$login = "bad";
	$login_message = "Unable to validate your e-mail address.<br><br>Please press back to try again."; 
	}

	//create a auth token
	$ispraak_time = time();
	$ispraak_token=substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1).substr(md5(time()),1);

	//make a cookie from hex version of email address
	$id_cookie = bin2hex($email); 
	setcookie("id_cookie", $id_cookie, time()+7200, '/'); 
	
	//get the IP address
	$visitor_ip = getIP();
	
	if ($login == "good")
	{
	
		//connect to the database
		$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

		if (mysqli_connect_errno())
		{
  			$login = "bad";
  			$login_message = "Unable to connect to database right now. <br><br>Please press back to try again."; 
		}
		else
		{
		//since there is no connection error, sanitize all the user input
		$email = mysqli_real_escape_string($msi_connect, $email);
	
		//define the query
		$query = "INSERT INTO ispraak_auth VALUES ('$email', '$ispraak_token', '$visitor_ip', '$ispraak_time','')";

		//execute the query and determine if it was a good insert
		$good_insert = mysqli_query($msi_connect, $query);

		if (!$good_insert)
		{
			//new activity was not inserted
			$login = "bad";
  			$login_message = "Unable to add to database right now. <br><br>Please press back to try again."; 
		}
		else
		{
			//prepare the email
	
			$subject = "iSpraak Authentication Request";
			$from = "iSpraak <ispraak.bot@ispraak.com>";
	
			$headers = array ('From' => $from,
  			'To' => $email,
  			'Subject' => $subject,
  			'Reply-To' => $mail_reply_address,
  			'MIME-Version' => $mail_mime,
  			'Content-type' => $mail_content,
  			'Date' => date('r', time()));
  		
			$smtp = Mail::factory('smtp',
  			array ('host' => $mail_host,
    		'port' => $mail_port,
    		'auth' => true,
    		'username' => $mail_username,
    		'password' => $mail_password));
        
    		$auth_body = "Your authentication request to iSpraak has been received. Please use the following link: $domain_name/login.php?token=$ispraak_token&email=$email";
    	
    		$mailz = $smtp->send($email, $headers, $auth_body);	
		}
			
		//end no DB problem IF statement
		}
	
		//end login good if statement
	}
	
//display form with information from the login_message variable, as defined above
	
echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 

<br><br><br><center>$login_message<br><br><br>
<br>		</div>		
			</p>

		</form>	
		$ispraak_footer
	</div>
	</body>
</html>";

	
}

if ($action == "signout")
{

//update the database token to be expired and redirect user to sign-in screen
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);
$query = "UPDATE ispraak_auth SET auth_time = '1634835540' WHERE token = '$ispraak_token' AND email = '$auth_email' "; 
$good_update = mysqli_query($msi_connect, $query);
$ispraak_token == ""; 
$newURL = "login.php?signedout";
header('Location: '.$newURL);

}



if ($ispraak_token == "")
{
//this means someone was redirected to this page to authenticate

echo "$ispraak_header
	
		<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"login.php?action=email\">
			
					<div class=\"form_description\">
		$ispraak_logo
			Welcome to iSpraak! In order to continue to the requested page, you will need to authenticate your account. We can e-mail you with a link to connect, or you can quick connect with a Google or Microsoft Login.<br><br>
		</div>	
			
				<ul >
			
					<li id=\"li_1\" >
		<label class=\"description\" for=\"student_name\">Your e-mail address</label>
		<div>
			<input id=\"user_email\" name=\"user_email\" class=\"element text medium\" type=\"text\" value=\"$start_name\"/> 
		</div><p class=\"guidelines_on\" id=\"guide_1\"><img src=\"images/google_logo.png\" width=\"30\" align=\"right\"><a href=\"redirect.php\" class=\"cutelink2\">Connect with my Google Account</a><br><br><img src=\"images/microsoft_logo.png\" width=\"30\" align=\"right\"><a href=\"azure_sso.php\" class=\"cutelink2\">Connect with my Microsoft Account</a></p> 
			
		</li>		
		
	<p hidden class=\"guidelines\" id=\"guide_1\"></p>
			
		
					<li class=\"buttons\">
			    <input type=\"hidden\" name=\"form_id\" value=\"1007732\" />
			    
				<input id=\"saveForm\" class=\"button5\" type=\"submit\" name=\"submit\" value=\"Get Link!\" />
		</li>
			</ul>

			
			
			
			
			<div class=\"alert\" id=\"alert\" style=\"display:none\">
  <span class=\"closebtn\" onclick=\"this.parentElement.style.display='none';\">&times;</span> 
  <strong>Warning: Google Chrome is required to use iSpraak!</strong>
</div>	
		</form>	
		$ispraak_footer
	</div>
	</body>
	
</html>";


}
else
{
//check token against iSpraak authentication database

//connect to the DB to verify this is a real activity

$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
}
else
{
//no database error
//check to see if the authenticatin token is good for this email address and IP and has not expired


$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_auth where token='$ispraak_token' AND email='$auth_email'");
$j = 0;
$auth_ip=mysqli_result($myresult,$j,"ip");
$auth_time=mysqli_result($myresult,$j,"auth_time");

//get the IP address to confirm client has not changed 
$visitor_ip = getIP();

//calculate expiration of token - let's add 8 hours to it
$auth_time_expire = $auth_time + 25200; 
$ispraak_time = time();


//check all the variables for a valid session

if ($visitor_ip != $auth_ip)
{
//it is possible the IP address changed and this is still a valid request
//might be a problem with Apple Private Replay or other VPNs

$id_cookie = $_COOKIE["id_cookie"]; 
$id_cookie_challenge = bin2hex($auth_email);

if ($id_cookie == $id_cookie_challenge)
{
	$visitor_ip = $auth_ip; 
	
}
}


if ($visitor_ip == $auth_ip && $auth_time_expire > $ispraak_time)
{

//this authentication is good, want to log this access

	$logrightnow = time();
	$logrightnow = date('m/d/Y H:i:s', $logrightnow);
	$mylogfile = fopen("activity_log.txt", "a") or die("Unable to open log file!");
	$logtxt = "\n $auth_email has successfully email authenticated at $logrightnow \n";
	fwrite($mylogfile, $logtxt);
	fclose($mylogfile);

	$hidden_admin = "";
	
	if ($ispraak_admin_email == $auth_email)
	{
		$hidden_admin = " | <a href=\"admin_view.php?email=$auth_email&token=$ispraak_token\" class=\"cutelink3\" class=\"cutelink3\"> Admin </a>";
	}


	echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=C\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/centersimple.css?v=G\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/pace.js\"></script></head>

<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<img src = \"images/gohome.png\" align=\"right\" width=\"40\">			

<br><br><br><center>You have successfully authenticated ($auth_email)<br>
<br>		</div>		
			</p><ul>
			
			
			<center><a href=\"roster.php?email=$auth_email&token=$ispraak_token\" class=\"cutelink3\" class=\"cutelink3\"> Roster </a> | <a href=\"sets.php?action=view&email=$auth_email&token=$ispraak_token\" class=\"cutelink3\">Sets</a> | <a href=\"modify.php?action=review&email=$auth_email&token=$ispraak_token\" class=\"cutelink3\">Edit</a> | <a href=\"delete.php?action=review&email=$auth_email&token=$ispraak_token\" class=\"cutelink3\">Delete</a> | <a href=\"preferences.php?action=review&email=$auth_email&token=$ispraak_token\" class=\"cutelink3\">Preferences</a>  | <a href=\"emails.php?email=$auth_email&token=$ispraak_token\" class=\"cutelink3\">Emails</a> | <a href=\"login.php?action=signout&email=$auth_email&token=$ispraak_token\" class=\"cutelink3\">Sign Out</a> $hidden_admin</center><br>
			
			";
			
//now show links for activities made with this email address

$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where email='$auth_email' ORDER BY mykey DESC");
$num=mysqli_num_rows($myresult);
			
if ($num < 1)
{
	echo "<br><center>Welcome! To make your first activity, start <a href=\"$domain_name\" class=\"cutelink3\">here</a>.<br>";
	echo "<br><center>To find existing activities, explore <a href=\"$domain_name/explore.php\" class=\"cutelink3\">here</a>.<br>";

}

$i=0;
while ($i < $num) 
{

$j = $i+1;
$key1=mysqli_result($myresult,$i,"mykey");
$key2=mysqli_result($myresult,$i,"mykey2");
$key3=mysqli_result($myresult,$i,"blocktext");
$key4=mb_strcut($key3, 0, 50, "UTF-8");
$key5 = $key4 . ""; 
$key6=date('m/d/y', $key1);
$key7="<a href=\"stats.php?mykey=$key1&instructor_email=$auth_email&mykey2=$key2\" class=\"cutelink3\" target=\"_blank\">Stats</a>";
$key8="<a href=\"sets.php?action=view&email=$auth_email&token=$ispraak_token\"><img src=\"images/gear.jpg\" width=\"15\" alt=\"Organize into set\"></a>";

$new_link = "$domain_name/ispraak.php?mykey=$key1&mykey2=$key2"; 
$copy_link = "<div class=\"tooltip\"><img src=\"images/copy_link.png\" class=\"smallicons\" height=\"15px\" width=\"15px\" id=\"$new_link\" onClick=\"clickURL(this.parentNode.children[0],myTooltip$j);\" onmouseout=\"outFunc(myTooltip$j)\"><span class=\"tooltiptext\" id=\"myTooltip$j\">Copy student link to clipboard</span></div>";

$grades_link = "<div class=\"tooltip\"><a href=\"$domain_name/grades.php?mykey=$key1&mykey2=$key2\" target=\"_blank\"><img src=\"images/grades.png\" class=\"smallicons\" height=\"15px\" width=\"15px\" id=\"$new_link\"></a><span class=\"tooltiptext\">See student grades</span></div>";
$stats_link = "<div class=\"tooltip\"><a href=\"stats.php?mykey=$key1&instructor_email=$auth_email&mykey2=$key2\" target=\"_blank\"><img src=\"images/stats_icon.png\" class=\"smallicons\" height=\"15px\" width=\"15px\" id=\"$new_link\"></a><span class=\"tooltiptext\">See activity stats</span></div>";
$launch_link = "<div class=\"tooltip\"><a href=\"ispraak.php?mykey=$key1&mykey2=$key2\" target=\"_blank\"><img src=\"images/launch.png\" class=\"smallicons\" height=\"15px\" width=\"15px\" id=\"$new_link\"></a><span class=\"tooltiptext\">Start activity as student</span></div>";


echo "<li>$key6 $copy_link $grades_link $stats_link $launch_link $key5</li>"; 



$i++;
}
	

			
echo "	</ul>		

		</form>	
		$ispraak_footer
	</div>

	</body>
</html>";
}
else
{
	if ($action != "email")
	{
	
	echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br><center>Sorry, we are unable to authenticate you right now.<br><br><br>$login_button
<br>		</div>		
			</p>

		</form>	
		$ispraak_footer
	</div>
	</body>
</html>";
}



}

//end no DB error IF statement
}

//end check authentication token
}

//close your connection to the DB
mysqli_close($msi_connect);


?>

