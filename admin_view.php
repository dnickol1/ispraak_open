<?php

/*

 This page provides iSpraak administrator with an overview of activity.
 Admin authentication is required to load this page. 
 Admin e-mail address stored in Config file.
  
*/

session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//Get session info from query string for this page

$ispraak_token=$_GET['token'];
$auth_email=$_GET['email'];

//Assume access is not yet granted 

$permission = "denied"; 

//Connect to the database and confirm access is allowed 

$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

//Escape query string variables 

$ispraak_token = mysqli_real_escape_string($msi_connect, $ispraak_token);
$auth_email = mysqli_real_escape_string($msi_connect, $auth_email);

//Check authentication

$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_auth where token='$ispraak_token' AND email='$auth_email'");
$j = 0;
$auth_time=mysqli_result($myresult,$j,"auth_time");
$auth_time_expire = $auth_time + 25200; 
$ispraak_time = time();
$two_days = ($ispraak_time - 172800); 
$one_week = ($ispraak_time - 604800); 
$one_month = ($ispraak_time - 2592000); 
$two_months = ($ispraak_time - 5270400); 

$auth_time_expire2 = ($ispraak_time - 25200); 

echo "$ispraak_header
	
<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
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

		
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey > '$two_days' ORDER BY mykey DESC");
		$num=mysqli_num_rows($myresult);	
			
		echo "<br>$num recently created activities for all instructors | <a href=\"admin_reassign.php?token=$ispraak_token&email=$auth_email\" class=\"cutelink3\">(Reassign)</a><br><br>";

				
		$i=0;
		while ($i < $num) 
		{

			$key1=mysqli_result($myresult,$i,"mykey");
			$key2=mysqli_result($myresult,$i,"mykey2");
			$key3=mysqli_result($myresult,$i,"blocktext");
			$key4=mb_strcut($key3, 0, 40, "UTF-8");
			$key5 = $key4 . ""; 
			$key6=date('m/d/y', $key1);
			$key7=mysqli_result($myresult,$i,"email");
			$key8=mysqli_result($myresult,$i,"language");
			echo "<li><a href=\"ispraak.php?mykey=$key1&mykey2=$key2\" target=\"_blank\" class=\"cutelink3\">$key6</a> | $key7 | $key8 | $key4 </li>"; 
			$i++;
		}
		//end while statement
		
	
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_grades where timestamp > '$two_days' ORDER BY timestamp DESC");
		$num2=mysqli_num_rows($myresult);			
		
		echo "<br>$num2 recently completed activities for all students: <br><br>";

		$i=0;
		while ($i < $num2) 
		{
			$sname=mysqli_result($myresult,$i,"student_name");
			$semail=mysqli_result($myresult,$i,"student_email");
			$sscore=mysqli_result($myresult,$i,"score");
			$seffort=mysqli_result($myresult,$i,"effort_text");
			$seffort2=mb_strcut($seffort, 0, 20, "UTF-8");
			$stime=mysqli_result($myresult,$i,"timestamp");
			$readable_date = date('m/d/y', $stime);
			$smissed=mysqli_result($myresult,$i,"missed_words");
			$said=mysqli_result($myresult,$i,"activity_id");
			$smisc=mysqli_result($myresult,$i,"misc");
			$ukey=mysqli_result($myresult,$i,"uniquekey");

			echo "<li><a href=\"grades.php?mykey=$said&mykey2=$smisc\" target=\"_blank\" class=\"cutelink3\">$readable_date</a> | $sname | $semail | $sscore | $seffort2 </li>"; 
			$i++;
		}
		//end while statement
		

		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_auth where auth_time > '$auth_time_expire2' ORDER BY record DESC");
		$num3=mysqli_num_rows($myresult);	
		
		echo "<br>$num3 currently authenticated users: <br><br>";
				
		$i=0;
		while ($i < $num3) 
		{
			$email=mysqli_result($myresult,$i,"email");
			$authtime=mysqli_result($myresult,$i,"auth_time");
			$auth_ip=mysqli_result($myresult,$i,"ip");
			$readable_date = date('m/d/Y H:i:s', $authtime);
			
			$details = json_decode(file_get_contents("http://ipinfo.io/".$auth_ip)); 
			$city = $details->city;
			$region = $details->region; 
			
			echo "<li>$readable_date CT | $email | $auth_ip | $city</li>"; 
			$i++;
		}
		//end while statement
		
		
		$directory = 'uploadmp3/';
		$files = glob($directory . '*.mp3');

		if ( $files !== false )
		{
    		$filecount = count( $files );
		}

		$mp3_files = $filecount;

		$directory = 'audio/';
		$files = glob($directory . '*.mp3');

		if ( $files !== false )
		{
    		$filecount = count( $files );
		}

		$tts_files = $filecount;

		echo "<br>$mp3_files user files provided (uploaded/recorded) and $tts_files TTS files generated<br>";	
		
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak");
		$num=mysqli_num_rows($myresult);	
		
		echo "<br>$num activities created<br>";	
		
		$myresult = mysqli_query($msi_connect, "SELECT DISTINCT email FROM ispraak WHERE email LIKE '%@%'");
		$num=mysqli_num_rows($myresult);	
		
		echo "<br>$num unique instructor e-mail addresses<br>";	
		
		$myresult = mysqli_query($msi_connect, "SELECT DISTINCT student_email FROM ispraak_grades WHERE student_email LIKE '%@%'");
		$num=mysqli_num_rows($myresult);	
		
		echo "<br>$num unique student e-mail addresses since 2020.<br>";	
		
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_stats");
		$num=mysqli_num_rows($myresult);
		
		echo "<br>$num missed words identified since 2023.<br>";	
				
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey > '$one_week' ORDER BY mykey DESC");
		$num=mysqli_num_rows($myresult);
		
		echo "<br>Activities made: past week ($num), ";	
		
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey > '$one_month' ORDER BY mykey DESC");
		$num=mysqli_num_rows($myresult);
		
		echo "past month ($num), "; 
		
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey > '$two_months' ORDER BY mykey DESC");
		$num=mysqli_num_rows($myresult);
		
		echo "past two months ($num) "; 
		
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_grades where timestamp > '$one_week' ORDER BY timestamp DESC");
		$num2=mysqli_num_rows($myresult);	
		
		echo "<br><br>Activities completed: past week ($num2), ";	
		
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_grades where timestamp > '$one_month' ORDER BY timestamp DESC");
		$num2=mysqli_num_rows($myresult);	
		
		echo "past month ($num2), "; 
		
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_grades where timestamp > '$two_months' ORDER BY timestamp DESC");
		$num2=mysqli_num_rows($myresult);	
		
		echo "past two months ($num2)"; 
		
		
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