<?php

/*

This page will build a custom activity set based on a provided email address and name. 
Based on prior activities, any assignment with a score < 85 will be included into the set.
If no completed activities have low score, user will be notified everything is already reviewed

*/

//Comment below lines out to stop error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Starts or continues session from previous PHP page
session_start();

//Get config file variables and functions
include_once("../../config_ispraak.php");

//Connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
  	$error = "Unable to connect to database."; 
}

$email=$_GET['email'] ?? 'NA';
$id=$_GET['id'] ?? 'NA';

//declare session variable for student email
$_SESSION['start_email'] = $email;
$_SESSION['start_name'] = $id;


$query_distinct=mysqli_query($msi_connect, "SELECT activity_id, score, timestamp,misc, effort_text FROM ispraak_grades where student_email='$email' ORDER BY activity_id DESC, timestamp DESC");
$num_distinct=mysqli_num_rows($query_distinct);
$row2 = mysqli_fetch_array($query_distinct);
$num2 =mysqli_num_rows($query_distinct);
$j=0;

//keep track if an activity is a duplicate and how many duplicates there are

$cached_activity_id = ".";
$multiple_submits = "1";
$high_score = "0";
$array1 = [];
$array2 = [];

while($j<$num2)
{

	$effort= mysqli_result($query_distinct,$j,"effort_text");
	$aid=mysqli_result($query_distinct,$j,"activity_id");
	$time=mysqli_result($query_distinct,$j,"timestamp");
	$date_end = date('m/d/y', $time);
	$mykey2=mysqli_result($query_distinct,$j,"misc");
	$score=mysqli_result($query_distinct,$j,"score");	
	$effort_text=mb_strcut($effort, 0, 35, "UTF-8");

	if ($cached_activity_id == $aid)
	{
		$multiple_submits++; 

		$effort_text = '<i><span style="background-color:#e6f7ff"> → '  . $effort_text . '</span></i>'; 
		
		if ($score > 85)
		{
		
			foreach (array_keys($array1, $aid, true) as $key) 
			{
    		unset($array1[$key]);
    		}
		
			foreach (array_keys($array2, $mykey2, true) as $key) {
    		unset($array2[$key]);
    		}
		
		}
				
	}
	else
	{
		$cached_activity_id = $aid; 
		$multiple_submits = "1";
		
		if ($score < 85)
		{
		array_push($array1, $aid);
		array_push($array2, $mykey2);
		}
		
	}
	
	$j++;

}

	
	//Encode both arrays as JSON
	
	$a1 = json_encode($array1); 
	$a2 = json_encode($array2); 
		
	//Store this array as a cookie
	
	setcookie("array1", $a1, time()+7200, '/'); 
	setcookie("array2", $a2, time()+7200, '/'); 

	//Add an aditional cookie so ispraak.php knows to display set wrapper
	
	$active_set = "true";
	setcookie("active_set", $active_set, time()+7200, '/'); 

	$len1=count($array1);
	$len2=count($array2);

	$_SESSION['len_1'] = $len1;
	$_SESSION['len_2'] = $len2;
	
	//because you have unset, you need to reindex arrays
	
	if ($len1 > 0)
	{
	
	$array1 = array_values($array1);
	$array2 = array_values($array2);
	
	$mykey = $array1[0];
	$mykey2 = $array2[0];
	
	$newURL = "ispraak.php?mykey=$mykey&mykey2=$mykey2"; 
	
	$message_button = "This custom set includes only activities that might need review.<br> <br>Today you have $len1 activities for review. 
<br><br>
You can track your progress at the bottom of the screen. 
<br>
<br>
<br>
<a href=\"$newURL\" class=\"button4\">Get Started!</a>
<br>
<br> "; 
	
	}
	else
	{
	
		$message_button = "Woot! Nothing needs review for $email! <br><br><br>"; 
	}



	
	echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br><br><center>

$message_button

		</div>		
			</p>

		</form>	
		$ispraak_footer
	</div>
	
<div class = \"activityset\" id=\"activityset\"> This set has $len1 activities remaining. </div>	
	
	</body>
</html>";
	



			
//close your connection to the DB
mysqli_close($msi_connect);

?>