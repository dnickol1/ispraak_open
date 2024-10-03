<?php

/*

 This page allows instructors to view and delete activities they have created.
 Note that this will only hide activities from their view, by changing the 
 instructor email to archive@ispraak.net
 
 Authentication is required to load this page. 
 
*/

session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//Get function from query string for this page
//Not every call to this page will need all these variables

$action=$_GET['action'] ?? 'review';
$email=$_GET['email'];
$mykey=$_GET['mykey'];
$mykey2=$_GET['mykey2'];
$ispraak_token=$_GET['token'];
$permission = "denied"; 
$auth_email=$_GET['email'];

//confirm access allowed to this page - check for email and token pair and expiry

//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_auth where token='$ispraak_token' AND email='$email'");
$j = 0;
$auth_time=mysqli_result($myresult,$j,"auth_time");
$auth_time_expire = $auth_time + 25200; 
$ispraak_time = time();

echo "$ispraak_header
	
<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
			$ispraak_logo
<a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
			<br><br><br>
					
";




if ($auth_time_expire > $ispraak_time)
{
$permission = "good";
}

if ($permission == "good")
{

	if ($action == "delete")
	{

		$mykey = mysqli_real_escape_string($msi_connect, $mykey);
		$mykey2 = mysqli_real_escape_string($msi_connect, $mykey2);
		$email = mysqli_real_escape_string($msi_connect, $email);

		//define the query
		$query = "UPDATE ispraak SET email = 'archive@ispraak.net' WHERE mykey = '$mykey' AND mykey2 = '$mykey2'"; 

		//execute the query and determine if it was a good insert
		$good_update = mysqli_query($msi_connect, $query);

		$action = "review"; 

		//end bracket for deleting instructor name from activity
	}

	if ($action == "review")
	{
	
		echo "<br>Attention: It is advised that you first remove unwanted activities from any sets you have created. Deleting an activity here will not remove it from the set.<br><br>";
		
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where email='$auth_email' ORDER BY mykey DESC");
		$num=mysqli_num_rows($myresult);			
		$i=0;
		while ($i < $num) 
		{
			$j = $i+1;
			$key1=mysqli_result($myresult,$i,"mykey");
			$key2=mysqli_result($myresult,$i,"mykey2");
			$key3=mysqli_result($myresult,$i,"blocktext");
			$key4=mb_strcut($key3, 0, 55, "UTF-8");
			//$key4=substr($key3, 0, 50);
			$key5 = $key4 . ""; 
			$key6=date('m/d/y', $key1);
			$key7="<a href=\"delete.php?action=delete&email=$auth_email&token=$ispraak_token&mykey=$key1&mykey2=$key2\" class=\"cutelink3\">DELETE</a>";
			echo "<li>$key6 | $key7 | $key5 </li>"; 
			$i++;
		}
		//end while statement
	//end review statement	
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

		
		
		
		
		
echo"			
			
		</form>	
		$ispraak_footer
	</div>
	</body>
	
</html>";


?>

