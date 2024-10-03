<?php

/*

 This page allows instructors to view and edit activities they have created.
 Authentication is required to load this page. 
 Editing an activity has no effect on existing student scores, which are not recalculated. 
  
*/

session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//Get function from query string for this page
//Not every call to this page will need all these variables

$edit_text=$_POST['edit_text'] ?? 'NA';

if ($edit_text == "NA")
{
$action=$_GET['action'] ?? 'review';
$email=$_GET['email'];
$mykey=$_GET['mykey'];
$mykey2=$_GET['mykey2'];
$ispraak_token=$_GET['token'];
$permission = "denied"; 
$auth_email=$_GET['email'];
}
else
{
$action=$_POST['action'] ?? 'review';
$email=$_POST['email'];
$mykey=$_POST['mykey'];
$mykey2=$_POST['mykey2'];
$ispraak_token=$_POST['token'];
$permission = "denied"; 
$auth_email=$_POST['email'];
}


//confirm access allowed to this page - check for email and token pair and expiry

//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_auth where token='$ispraak_token' AND email='$email'");
$j = 0;
$auth_time=mysqli_result($myresult,$j,"auth_time");
$auth_time_expire = $auth_time + 25200; 
$ispraak_time = time();

//see if Flexible Scoring is enabled and activiate button if it is.

$flexible_button = ""; 
$flexible_scoring = "Strict"; 
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_user_prefs2 where email='$email' ORDER BY id DESC");
$rowcount=mysqli_num_rows($myresult);	
if ($rowcount > 0) { $flexible_scoring =mysqli_result($myresult,0,"pref_02"); }
if ($flexible_scoring == "Flexible" && $action == "edit" || $action=="flex")
{

	//insert button on the edit view for flexible scoring
	$flexible_button = "<a href=\"modify.php?token=$ispraak_token&email=$email&action=flex&mykey=$mykey&mykey2=$mykey2\" class=\"cutelink3\"><img src = \"images/flex.png\" align=\"right\" width=\"40\"></a>";

	//let's  check for the best existing score on this activity
	$result_flexible_score = mysqli_query($msi_connect, "SELECT * FROM ispraak_grades WHERE activity_id='$mykey' ORDER BY score DESC");
	$row_flexible_score = mysqli_fetch_array($result_flexible_score);
	$num_flexible_score = $result_flexible_score->num_rows;
	
	$top_text = "Replace this text with alternate text for this activity.";
	$top_score = "0";
	
	if ($num_flexible_score > 0) 
	{
		$index_flexible = 0; 
		$top_score=mysqli_result($result_flexible_score,$index_flexible,"score");
		$top_text=mysqli_result($result_flexible_score,$index_flexible,"effort_text");
	}

	//you now have TOP TEXT and TOP SCORE 

}

//end of flexible scoring


echo "$ispraak_header
	
<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
			$ispraak_logo
<a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
		$flexible_button
			<br><br><br>
					
";


if ($auth_time_expire > $ispraak_time)
{
$permission = "good";
}

if ($permission == "good")
{

	if ($action == "edit")
	{

		$mykey = mysqli_real_escape_string($msi_connect, $mykey);
		$mykey2 = mysqli_real_escape_string($msi_connect, $mykey2);
		$email = mysqli_real_escape_string($msi_connect, $email);

		//define the query
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey = '$mykey' AND mykey2 = '$mykey2'");
		$text=mysqli_result($myresult,0,"blocktext");
		$language=mysqli_result($myresult,0,"language");
			
		
		//remind user that a TTS file was generated with the previous text
		$file_exists_warning = "<li><p class = \"guidelines_on2\">Changes made here will not affect existing student scores or aggregate activity stats.</p></li>"; 
		$tts_file_exists= 'audio/'.$mykey.'_'.$mykey2.'.mp3'; 
		if (file_exists($tts_file_exists))
		{
			$file_exists_warning = "<li><p class = \"guidelines_on2\">Warning: A TTS audio file has already been generated for this activity. Editing text here does not change the audio.</p></li>"; 
		}


		//show editing window
		
		echo "
		
		<form id=\"iSpraak\" class=\"ispraak_form\"  method=\"post\" action=\"modify.php\">
		
		<ul>
		
		$file_exists_warning
		
			<textarea id=\"edit_text\" name=\"edit_text\" id=\"edit_text\" class=\"element textarea modify\" maxlength=\"370\">$text</textarea> 
				
			<br><br><center>
			<input id=\"saveForm\" class=\"button4\" type=\"submit\" name=\"submit\" value=\"Edit Activity\" /></center>
			</center>
			  <input type=\"hidden\" name=\"action\" id=\"action\" value=\"update\"/> 
			  <input type=\"hidden\" name=\"email\" id=\"email\" value=\"$auth_email\"/> 
			  <input type=\"hidden\" name=\"token\" id=\"token\" value=\"$ispraak_token\"/> 
			  <input type=\"hidden\" name=\"mykey\" id=\"mykey\" value=\"$mykey\"/> 
			  <input type=\"hidden\" name=\"mykey2\" id=\"mykey2\" value=\"$mykey2\"/> 
		
		</ul>
		</form>	
		<br>
		
		
		
		";
		
		
		//adjust display for RTL languages
		 
		if($language=='ar' || $language=='fa' || $language=='he' || $language=='ur')
		{
			echo"<script>document.getElementById(\"edit_text\").style.textAlign = \"right\"</script>";

		}
		else
		{
			echo"<script>document.getElementById(\"edit_text\").style.textAlign = \"left\"</script>";
		}

		
		
		
		//end bracket for editing instructor name from activity
	}


//new action for flexible scoring edit

if ($action == "flex")
	{

		$mykey = mysqli_real_escape_string($msi_connect, $mykey);
		$mykey2 = mysqli_real_escape_string($msi_connect, $mykey2);
		$email = mysqli_real_escape_string($msi_connect, $email);

		//define the query
		$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey = '$mykey' AND mykey2 = '$mykey2'");
		$text=mysqli_result($myresult,0,"blocktext");
		$language=mysqli_result($myresult,0,"language");
		
		if ($top_score == 100)
		{
			$flex_note = "Perfect scores found. This activity may not need alternate text. "; 
		
		}
		else
		{
			$flex_note = "Current best score is: $top_score. Suggested text in box.";
		
		}
		
		if ($top_score == 0)
		{
			$top_text = "$text"; 
		}
		
		//check to see if there is already a flexible text on record
		$myresult2 = mysqli_query($msi_connect, "SELECT * FROM ispraak_flex where mykey = '$mykey' AND mykey2 = '$mykey2' ORDER BY record DESC");
		$num_flexible_texts = $myresult2->num_rows;
		if ($num_flexible_texts > 0)
		{
			$flex_note = "Existing flexible text already saved. ";
			$top_text=mysqli_result($myresult2,0,"flex_text");
		}
		
		
		//show editing window
		
		echo "
		
		<form id=\"iSpraak\" class=\"ispraak_form\"  method=\"post\" action=\"modify.php\">
		
		<ul>
		
		<li><p class = \"guidelines_on2\">$flex_note</p></li>
		
			<textarea id=\"edit_text\" name=\"edit_text\" id=\"edit_text\" class=\"element textarea modify\" maxlength=\"370\">$top_text</textarea> 
				
			<br><br><center>
			<input id=\"saveForm\" class=\"button4\" type=\"submit\" name=\"submit\" value=\"Edit Flexible Scoring Text\" /></center>
			</center>
			  <input type=\"hidden\" name=\"action\" id=\"action\" value=\"update_flex\"/> 
			  <input type=\"hidden\" name=\"email\" id=\"email\" value=\"$auth_email\"/> 
			  <input type=\"hidden\" name=\"token\" id=\"token\" value=\"$ispraak_token\"/> 
			  <input type=\"hidden\" name=\"mykey\" id=\"mykey\" value=\"$mykey\"/> 
			  <input type=\"hidden\" name=\"mykey2\" id=\"mykey2\" value=\"$mykey2\"/> 
		
		</ul>
		</form>	
		<br>
		
		
		
		";
		
		
		//adjust display for RTL languages
		 
		if($language=='ar' || $language=='fa' || $language=='he' || $language=='ur')
		{
			echo"<script>document.getElementById(\"edit_text\").style.textAlign = \"right\"</script>";

		}
		else
		{
			echo"<script>document.getElementById(\"edit_text\").style.textAlign = \"left\"</script>";
		}

		
		
		
		//end bracket for editing instructor name from activity
	}


	if ($action == "update_flex")
	{
	
		$mykey = mysqli_real_escape_string($msi_connect, $mykey);
		$mykey2 = mysqli_real_escape_string($msi_connect, $mykey2);
		$edit_text = mysqli_real_escape_string($msi_connect, $edit_text);
	
		//define query and check for error
		$query = "INSERT INTO ispraak_flex VALUES ('$edit_text','$mykey', '$mykey2','')";
		$good_insert = mysqli_query($msi_connect, $query);

		$action = "review";
	}










	if ($action == "update")
	{

		$mykey = mysqli_real_escape_string($msi_connect, $mykey);
		$mykey2 = mysqli_real_escape_string($msi_connect, $mykey2);
		$email = mysqli_real_escape_string($msi_connect, $email);
		$edit_text = mysqli_real_escape_string($msi_connect, $edit_text);
		
		//define the query
		if((strlen($edit_text)<500) && $edit_text == strip_tags($edit_text) && strpos($edit_text,'http') == false && strpos($edit_text,'www') == false && strpos($edit_text,'.com') == false && strpos($edit_text,'.net') == false && strpos($edit_text,'.org') == false && strpos($edit_text,'bit.ly') == false) 
		{

		$query = "UPDATE ispraak SET blocktext = '$edit_text' WHERE mykey = '$mykey' AND mykey2 = '$mykey2'"; 

		//execute the query and determine if it was a good insert
		$good_update = mysqli_query($msi_connect, $query);

		$action = "review"; 
		}
		else
		{
			echo"<center><br><p>Unable to update activity. Please try a shorter text without any HTML tags or other special characters!</p></center>";
		}

		//end bracket for updating instructor name from activity
	}


	if ($action == "review")
	{
	
		echo "<br>Attention: Editing an activity here will not affect existing student scores or audio files.<br><br>";
		
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
			$key7="<a href=\"modify.php?action=edit&email=$auth_email&token=$ispraak_token&mykey=$key1&mykey2=$key2\" class=\"cutelink3\">EDIT</a>";
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

