<?php

/*

 Admin page for iSpraak admin to reassign email address for activities
 Admin authentication is required to load this page. 
 Admin e-mail address stored in Config file.
  
*/

session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//Get session info from query string for this page

$ispraak_token=$_GET['token'];
$auth_email=$_GET['email'];

$change_from=$_GET['change_from'] ?? "unknown";
$change_to=$_GET['change_to'] ?? "unknown";
$first=$_GET['first'] ?? "1000000081 ";
$last=$_GET['last'] ?? "9000000081";
$update=$_GET['update'] ?? "false";

//Check if the search form has been submitted just now
$update2=$_POST['update2'];

//If it has been submitted, populate variables from form rather than from query string
if ($update2 == "true")
	{
		$ispraak_token=$_POST['ispraak_token'];
		$auth_email=$_POST['auth_email'];
		$change_from=$_POST['change_from'] ?? "unknown";
		$change_to=$_POST['change_to'] ?? "unknown";
		$first=$_POST['first'] ?? "1000000081 ";
		$last=$_POST['last'] ?? "9000000081";
	}

if ($first == "") { $first = "1000000081"; }
if ($last == "") { $last = "9000000081"; }

//Assume access is not yet granted 

$permission = "denied"; 

//Connect to the database and confirm access is allowed 

$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

//Escape query string variables 

$ispraak_token = mysqli_real_escape_string($msi_connect, $ispraak_token);
$auth_email = mysqli_real_escape_string($msi_connect, $auth_email);

//Check auth token

$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_auth where token='$ispraak_token' AND email='$auth_email'");
$j = 0;
$auth_time=mysqli_result($myresult,$j,"auth_time");
$auth_time_expire = $auth_time + 25200; 
$ispraak_time = time();
$two_days = ($ispraak_time - 172800); 
$auth_time_expire2 = ($ispraak_time - 25200); 

echo "$ispraak_header
	
<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"admin_reassign.php\">
			$ispraak_logo
<a href=\"login.php?token=$ispraak_token&email=$auth_email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
			<br><br><br>
					
";


if ($auth_time_expire > $ispraak_time)
{
$permission = "good";
}

if ($permission == "good" && $auth_email == "$ispraak_admin_email")
{

		
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where email = '$change_from' AND mykey > '$first' AND mykey < '$last' ORDER BY mykey DESC");
		$num=mysqli_num_rows($myresult);	
			
		echo "<br>$num activities available to reassign for $change_from to $change_to <br>for activity range $first to $last<br><br>";
		
		if ($num < 1)
		{
			echo "
			
			
								<li id=\"li_1\" >
		<label class=\"description\" for=\"x1\">Current owner email address</label>
		<div>
			<input id=\"change_from\" name=\"change_from\" maxlength=\"250\" class=\"element text medium\" type=\"text\"/> 
		</div><p class=\"guidelines\" id=\"guide_1\">Enter current ownership email address.
			
		</li>		
				
								<li id=\"li_1\" >
		<label class=\"description\" for=\"x2\">Future owner email address</label>
		<div>
			<input id=\"change_to\" name=\"change_to\" maxlength=\"250\" class=\"element text medium\" type=\"text\"/> 
		</div><p class=\"guidelines\" id=\"guide_1\">Enter new ownership email address. 
			
		</li>	
		
								<li id=\"li_1\" >
		<label class=\"description\" for=\"x3\">Starting activity number</label>
		<div>
			<input id=\"first\" name=\"first\" maxlength=\"100\" class=\"element text medium\" type=\"text\"/> 
		</div><p class=\"guidelines\" id=\"guide_1\">First number in range of series of activities. 
			
		</li>	
		
										<li id=\"li_1\" >
		<label class=\"description\" for=\"x4\">Ending activity number</label>
		<div>
			<input id=\"last\" name=\"last\" maxlength=\"100\" class=\"element text medium\" type=\"text\"/> 
		</div><p class=\"guidelines\" id=\"guide_1\">Last number in range of series of activities.  
			
		</li>	
				
		<input type=\"hidden\" id=\"ispraak_token\" name=\"ispraak_token\" value=\"$ispraak_token\"/>
		<input type=\"hidden\" id=\"auth_email\" name=\"auth_email\" value=\"$auth_email\"/>
		<input type=\"hidden\" id=\"update2\" name=\"update2\" value=\"true\"/>

		<input id=\"saveForm\" class=\"button5\" type=\"submit\" name=\"submit\" value=\"Search\" />
		
			
			";
		
		//Query should be: &change_from=old@ispraak.net&change_to=new@ispraak.net&first=10&last=20 
		
		}

		$i=0;
		while ($i < $num) 
		{

			$key1=mysqli_result($myresult,$i,"mykey");
			$key2=mysqli_result($myresult,$i,"mykey2");
			$key3=mysqli_result($myresult,$i,"blocktext");
			$key4=mb_strcut($key3, 0, 20, "UTF-8");
			$key5 = $key4 . ""; 
			$key6=date('m/d/y', $key1);
			$key7=mysqli_result($myresult,$i,"email");
			$key8=mysqli_result($myresult,$i,"language");
			echo "<li>$key1 | $key6 | $key7 | $key8 | $key4 </li>"; 
			$i++;
		}
		//end while statement
		
		if ($num > 0)
		{
			echo "<br><br>Do you want to update the email address for this range of activities?<br><br> ";
			echo "<a href=\"admin_reassign.php?token=$ispraak_token&email=$auth_email&change_from=$change_from&change_to=$change_to&first=$first&last=$last&update=true\" class=\"cutelink3\">Update All</a> (Cannot undo action)";
		
		}
		
		if ($update == "true")
		{
			echo "<br><br>permanent changes effected on $num records<br><br>"; 
			
			
				$i=0;
				while ($i < $num) 
				{

					$key1=mysqli_result($myresult,$i,"mykey");
					$key2=mysqli_result($myresult,$i,"mykey2");
					$key3=mysqli_result($myresult,$i,"blocktext");
					$key4=mb_strcut($key3, 0, 20, "UTF-8");
					$key5 = $key4 . ""; 
					$key6=date('m/d/y', $key1);
					$key7=mysqli_result($myresult,$i,"email");
					$key8=mysqli_result($myresult,$i,"language");
					echo "<li>$key1 | $key6 | $change_to | $key8 | $key4 </li>"; 
					$query = "UPDATE ispraak SET email = '$change_to' WHERE mykey = '$key1' AND mykey2 = '$key2' AND email = '$change_from'"; 
					//execute the query and determine if it was a good insert
					$good_update = mysqli_query($msi_connect, $query);
					$i++;
									
				}
		}
		
	
//end permission is good 
}
else
{

	echo "<center>Sorry, we are unable to authenticate you right now.<br><br><br>
<a href=\"login.php\" class=\"button4\">Login Page</a>
<br>
<br>		
			</p><br><br><br>";

}

//close your connection to the DB
mysqli_close($msi_connect);

		
//display bottom of form 		
				
echo"			
			
		</form>	
		$ispraak_footer
	</div>
	</body>
	
</html>";


?>

