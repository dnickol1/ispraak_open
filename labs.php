<?php

/*

iSpraak Labs allows instructors to try experimental features of the platform.

Authentication is required. 

*/

session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");


//Get function from query string for this page
//Not every call to this page will need all these variables

$action=$_GET['action'];
$email=$_GET['email'];
$ispraak_token=$_GET['token'];
$permission = "denied"; 

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


	if ($action == "update")
	{
		//get update variables from the form
		
		$pref_01=$_POST['element_3'] ?? 'NA';
		$pref_02=$_POST['element_4'] ?? 'NA';
		$pref_03=$_POST['element_1'] ?? 'NA';
		$pref_04=$_POST['element_6'] ?? 'NA';
		$pref_05="NA";
		
		$email=$_POST['email'];
		
		//define the query
		$query = "INSERT INTO ispraak_user_prefs2 VALUES ('$email', '$pref_01', '$pref_02', '$pref_03','$pref_04','$pref_05','')";

		//execute the query and determine for debugging if it was a good insert
		$good_insert = mysqli_query($msi_connect, $query);
		
				//echo("Error description: " . mysqli_error($msi_connect));
		
		
		//close your connection to the DB
		mysqli_close($msi_connect);

		//redirect

		$newURL = "login.php?action=view&email=$email&token=$ispraak_token";
		header('Location: '.$newURL);
		
	}


	if ($action == "reset")
	{
	
		//define the query
		$query = "UPDATE ispraak_user_prefs2 SET email = 'deleted' WHERE email = '$email' "; 

		//execute the query and determine if it was a good insert
		$good_update = mysqli_query($msi_connect, $query);
	
		//close your connection to the DB
		mysqli_close($msi_connect);

		//redirect

		$newURL = "login.php?action=view&email=$email&token=$ispraak_token";
		header('Location: '.$newURL);

	}

//begin output

echo "$ispraak_header <form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"labs.php?action=update&email=$email&token=$ispraak_token\">$ispraak_logo
			<a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
	
<br><br><br>"; 

echo "iSpraak Labs contains additional settings that are currently under active development. Please send feedback to us at help@ispraak.net<br><br>";


if (mysqli_connect_errno())
{
  	$error_saving_db = "<p style=\"color:red\">Database connection: X</span>";
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}
else
{
//since there is no connection error, prepare input for first query

$email = mysqli_real_escape_string($msi_connect, $email);

//do iSpraak lab preferences exist for this user? 

$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_user_prefs2 where email='$email' ORDER BY id DESC");
$rowcount=mysqli_num_rows($myresult);	

	$myorg = substr($email, strpos($email, "@") + 0);    
	$default_1 = "Select";
	$default_2 = "Select";
	$default_3 = "Select";
	$default_4 = "Select";
	$default_5 = "Select";

if ($rowcount < 1)
{
	//nothing found already saved for this user
	
}
else
{
	$i = 0;
	$default_1=mysqli_result($myresult,$i,"pref_01");
	$default_2=mysqli_result($myresult,$i,"pref_02");
	$default_3=mysqli_result($myresult,$i,"pref_03");
	$default_4=mysqli_result($myresult,$i,"pref_04");
	$default_5=mysqli_result($myresult,$i,"pref_05");
			
}

//close your connection to the DB
mysqli_close($msi_connect);

//Now we continue output and display a form with all Labs preferences

echo "


<ul>
<li id=\"li_3\" >
<label class=\"description\" for=\"element_3\">Roster Filtering</label>
<div>
<select class=\"element select medium\" id=\"element_3\" name=\"element_3\"> 
<option value=\"$default_1\" selected=\"selected\">$default_1</option>
<option value=\"@\" >Allow all e-mails</option>
<option value=\"$myorg\" >Limit to $myorg</option>
</select><p class=\"guidelines\" id=\"guide_3\">Filter out students not part of your institution from your roster.</p>
</div> 
</li>	
		
		
<li id=\"li_4\" >
<label class=\"description\" for=\"element_4\">Student Scoring</label>
<div>
<select class=\"element select medium\" id=\"element_4\" name=\"element_4\"> 
<option value=\"$default_2\" selected=\"selected\">$default_2</option>
<option value=\"Strict\">Strict Scoring</option>
<option value=\"Flexible\">Flexible Scoring</option>
</select>		
<p class=\"guidelines\" id=\"guide_4\">Flexible scoring is adjusted in EDIT mode through the TOOL icon.</p>
</li>	
		
				
<li id=\"li_5\" >
<label class=\"description\" for=\"element_1\">LTI Link Generation</label>
		
<div>
<select class=\"element select medium\" id=\"element_1\" name=\"element_1\"> 
<option value=\"$default_3\" selected=\"selected\">$default_3</option>
<option value=\"Enabled\" >Enabled</option>
<option value=\"Disabled\" >Disabled</option>
</select>		
	
<p class=\"guidelines\" id=\"guide_1\">This will create a LMS-specific link for each iSpraak set you've made.</p> 
		</li>	
			
			
<li id=\"li_6\" >
<label class=\"description\" for=\"element_6\">IPA Stats</label>
		
<div>
<select class=\"element select medium\" id=\"element_1\" name=\"element_6\"> 
<option value=\"$default_4\" selected=\"selected\">$default_4</option>
<option value=\"Enabled\" >IPA Enabled</option>
<option value=\"Disabled\" >IPA Disabled</option>
</select>		
	
<p class=\"guidelines\" id=\"guide_1\">Enable or disable IPA stats (limited language support)</p> 
		</li>	
		
					
			
			
<li class=\"buttons\">
 <input type=\"hidden\" name=\"form_id\" value=\"1007732\" />
<input type=\"hidden\" name=\"email\" id=\"email\" value=\"$email\" />
 <input type=\"hidden\" name=\"token\" id=\"token\" value=\"$ispraak_token\" />
			    
				<input id=\"saveForm\" class=\"button5\" type=\"submit\" name=\"submit\" value=\"Update Preferences\" />
		</li>
			</ul>

	
			</p>

		</form>	
		$ispraak_footer
	</div>
	</body>
	
</html>";



//above this bracket user is authenticated
}
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

