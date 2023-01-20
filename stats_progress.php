<?php

/*

This page shows average student progress across multiple attempts for any given activity. 

Requires mykey, mykey2, and instructor e-mail address to load.

Authentication is not required to load or share this page with the URL. 

*/

//Comment below lines out to stop error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
//Starts or continues session from previous PHP page

//Get config file variables and functions
include_once("../../config_ispraak.php");

//Connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

//Assume there is no error fetching grades
$error = ""; 

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
  	$error = "Unable to connect to database."; 
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}

//Example: https://www.ispraak.net/stats.php?mykey=1662152565&instructor_email=dnickol1@slu.edu&mykey2=Jb12984f0ba8d4740ef280bad88f73e7

//Get mykey from query string & declare session variable
//If no variable found in query string, just initialize as Not Available
$mykey = $_GET['mykey'] ?? 'NA';
$mykey2 = $_GET['mykey2'] ?? 'NA';
$email = $_GET['instructor_email'] ?? 'NA';

$readable_date=date('m/d/y', $mykey);


//Get all student grades from the specified activity and group by their email
$myresult = mysqli_query($msi_connect, "SELECT DISTINCT * FROM ispraak_grades WHERE activity_id = '$mykey' ORDER BY student_name ASC, activity_id ASC");
$num=mysqli_num_rows($myresult);

if ($num < 1)
{
  	$error = "<br>Sorry, there are no progress statistics available for this activity.<br><br>"; 
    echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=3\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br>$error";

}
else{


echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>  
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=3\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script>
</head>
 
<body id=\"main_body\" >
  
   <img id=\"top\" src=\"images/top.png\">
   <div id=\"form_container\">
   <div id=\"headerBar\"></div>
  
       <form id=\"form_1007732\" class=\"ispraak_form\" enctype=\"multipart/form-data\" method=\"post\" action=\"makeit.php\">
                   <div class=\"form_description\">
                  
                   <img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\">

			<br><br><br>Student improvement stats since $readable_date for $email ($num total records)</b> 
			
			<br></p>
						
		</div>						
			<ul >";
			
echo "<a href=\"stats.php?mykey=$mykey&mykey2=$mykey2&instructor_email=$email\" class=\"cutelink3\">Missed Words Stats</a> / <SPAN STYLE=\"background-color: #E6E6E6\">Progress Stats</span><br><br>"; 


//Initative caching system to store email, mykey, top scores, score array, and timestamp arrays
$cached_email = ".";
$cached_activity_id = ".";
$cached_top_score = "0";
$cached_score_array = [];
$cached_low_timestamp = "0";
$cached_high_timestamp = "0";
$cached_differences = []; 


		echo "<div class=\"tbl\">
		
		<div class=\"col\"><div class=\"cell\">Student ID</div></div>
		<div class=\"col\"><div class=\"cell\">First score</div></div>
		<div class=\"col\"><div class=\"cell\">Final score</div></div>
		<div class=\"col\"><div class=\"cell\">Highest score</div></div>
		
		<div class=\"col\"><div class=\"cell\">Attempts</div></div>
		
		</div><br>
		
		  ";



//Loop through Data 
$i=0;
while ($i < $num) 
{

$sname=mysqli_result($myresult,$i,"student_name");
$semail=mysqli_result($myresult,$i,"student_email");
$sscore=mysqli_result($myresult,$i,"score");
$seffort=mysqli_result($myresult,$i,"effort_text");
$stime=mysqli_result($myresult,$i,"timestamp");
$readable_date = date('m/d/y', $stime);
$smissed=mysqli_result($myresult,$i,"missed_words");
$smisc=mysqli_result($myresult,$i,"misc");
$ukey=mysqli_result($myresult,$i,"uniquekey");
$aid=mysqli_result($myresult,$i,"activity_id");


if ($cached_activity_id == $aid && $cached_email == $semail)
{
	
	//since this is a duplicate score for the same email address, put the score into an array 
	array_push($cached_score_array, $sscore);
	

}
else
{

	//now you have moved onto the next student record (different e-mail address)
	//if the score array has more than one item, then figure out what the max is, along with what is the first item
	$count_items = count($cached_score_array);
	if ($count_items > 1)
	{
		$max = max($cached_score_array);
		$first_item = $cached_score_array[0]; 
		$improvement = ($max - $first_item); 
		$last_item = end($cached_score_array); 
		array_push($cached_differences, $improvement); 
		
		echo "<div class=\"tbl\">
		
		<div class=\"col\"><div class=\"cell\">#$i</div></div>
		<div class=\"col\"><div class=\"cell\">$first_item</div></div>
		<div class=\"col\"><div class=\"cell\">$last_item</div></div>
		<div class=\"col\"><div class=\"cell\">$max </div></div>
		<div class=\"col\"><div class=\"cell\">$count_items</div></div>
		
		</div>
		
		  ";
		  
	}

	//reset arrays and caches for next student 

	$cached_activity_id = $aid; 
	$cached_score_array = []; 
	array_push($cached_score_array, $sscore);
	$cached_email = $semail; 
}


$i++;
}

echo "<hr>"; 

$var1 = array_sum($cached_differences);
$var2 = count($cached_differences);


if ($var1 == "0" || $var2 == "0")
{
echo "<br>Insufficient data to calculate progress stats on this activity."; 
}
else
{
$average = array_sum($cached_differences)/count($cached_differences);
echo "<br>Average improvement of $average % across all records for this activity <i>(first vs. highest)</i>.";
 
}

echo "<br><br>For this report to be useful, students must complete multiple submissions of this activity.";


			
echo "

			</ul>
		</form>	
		<div id=\"footer\">
			© D. Nickolai
		</div>
	</div>
	</body>
</html>";

//close your connection to the DB
mysqli_close($msi_connect);
}


?>