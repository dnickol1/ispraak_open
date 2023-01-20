<?php

/*

This page initializes an activity set based on the set_id in the query string of the URL. 
All activities corresponding to the set are saved into JSON encoded arrays and then as cookies.
An additional cookie, active_set, lets review.php know that a set is being completed.

*/

session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//Get set ID from query string 

$set_id=$_GET['id'];

//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	$error_saving_db = "<p style=\"color:red\">Database connection: X</span>";
  	
}
else
{
	//since there is no connection error, sanitize all the user input from query string 
	$set_id = mysqli_real_escape_string($msi_connect, $set_id);
	$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_sets where set_id='$set_id'");
	$i = 0; 
	$set_name=mysqli_result($myresult,$i,"set_name");
	$final_name = $set_name; 
	$set_email=mysqli_result($myresult,$i,"email");
	$rowcount_1=mysqli_num_rows($myresult);	
	
	$myresult2 = mysqli_query($msi_connect, "SELECT * FROM ispraak_sets where email='$set_email' AND set_name='$set_name' AND mykey !='empty'");
	$rowcount_2=mysqli_num_rows($myresult2);	

	$_SESSION['rowcount']=$rowcount_2;
	
	//Create an array of activities for this set
	
	for ($i = 0; $i < $rowcount_2; $i++) 
	{
    	$activity_array[] = $set_name=mysqli_result($myresult2,$i,"mykey");
    	$activity_array2[] = $set_name=mysqli_result($myresult2,$i,"mykey2");
    	
	}
		
	//Encode theses arrays as JSON
	
	$a1 = json_encode($activity_array); 
	$a2 = json_encode($activity_array2); 
	
	//Do not store any cookies AFTER an ECHO output occurs
	
	//Store this array as a cookie
	
	setcookie("array1", $a1, time()+7200, '/'); 
	setcookie("array2", $a2, time()+7200, '/'); 

	//Add an aditional cookie so ispraak.php knows to display set wrapper
	
	$active_set = "true";
	setcookie("active_set", $active_set, time()+7200, '/'); 

	$len1=count($activity_array);
	$len2=count($activity_array2);

	$_SESSION['len_1'] = $len1;
	$_SESSION['len_2'] = $len2;

	
	//If you need to retrieve cookie, you must decode JSON array | $data = json_decode($_COOKIE['your_cookie_name'], true)
		
	//Redirect to the first activity in the set 
	
	$i=0; 
	$mykey=mysqli_result($myresult2,$i,"mykey");
	$mykey2=mysqli_result($myresult2,$i,"mykey2");
	$newURL = "ispraak.php?mykey=$mykey&mykey2=$mykey2"; 
	
	echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br><center>Welcome! You are about to begin *$final_name*<br><br>This collection has $rowcount_2 activities.<br><br>
You can track your progress at the bottom of the screen. 
<br>
<br>
<br>
<br>
<a href=\"$newURL\" class=\"button4\">Get Started!</a>
<br>
<br>		</div>		
			</p>

		</form>	
		$ispraak_footer
	</div>
	
<div class = \"activityset\" id=\"activityset\"> This set has $rowcount_2 activities remaining. </div>	
	
	</body>
</html>";
	
	
	
}

//close your connection to the DB
mysqli_close($msi_connect);






?>

