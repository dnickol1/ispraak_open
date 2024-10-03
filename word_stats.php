<?php

/*

This page shows statistical frequency of a single word across multiple
activities assigned by the instructor. Requires missed_word variable
and can also take optional instructor e-mail address to load.

Authentication is not required to load or share this page with the URL. 

*/

session_start();
//Starts or continues session from previous PHP page

//Get config file variables and functions
include_once("../../config_ispraak.php");

//Connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

//Assume there is no error fetching from database
$error = ""; 

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
  	$error = "Unable to connect to database."; 
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}

//Get mykey from query string & declare session variable
//If no variable found in query string, just initialize as Not Available
$mykey = $_GET['mykey'] ?? 'NA';
$mykey2 = $_GET['mykey2'] ?? 'NA';
$email = $_GET['instructor_email'] ?? 'NA';
$missed_word = $_GET['missed_word'] ?? 'NA';
$missed_word_escaped = stripslashes($missed_word);

//escape words
$missed_word = mysqli_real_escape_string($msi_connect, $missed_word);

//Get info from iSpraak GRADES table
$result = mysqli_query($msi_connect,"SELECT * FROM ispraak_stats WHERE missed_word='$missed_word' AND instructor_email='$email' ORDER BY missed_word ASC");
$result2 = mysqli_query($msi_connect,"SELECT DISTINCT activity_id FROM ispraak_stats WHERE missed_word='$missed_word' AND instructor_email='$email' ORDER BY missed_word ASC");

$num=mysqli_num_rows($result);
$num2=mysqli_num_rows($result2);
$student_counter = 0; 

$temail_hide = hide_email($email);
 

if ($num < 1)
{
  	$error = "<br>Sorry, there are no statistics available for this particular word for your students.<br><br>"; 
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
else
{

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

			<br><br><br>Quick lexical stats for the word <i><b>$missed_word_escaped</b></i> for $temail_hide</b> 
			
			<br></p>
						
		</div>						
			<ul >";

//create a counter for student count
$student_counter = 0; 

//for each unique activity ID, check how many students have submitted something
for ($i = 0; $i < $num2; $i++)
	{
  
 	 	$mykey=mysqli_result($result2,$i,"activity_id");
 	 	$result3 = mysqli_query($msi_connect,"SELECT * FROM ispraak_grades WHERE activity_id='$mykey' AND teacher_email='$email'");
		$num3=mysqli_num_rows($result3);
  		//echo "$mykey has $num3 students trying this word<br> "; 
 		$student_counter = ($student_counter + $num3); 
	}



for ($i = 0; $i < $num; $i++)
	{
  
 	 	$mykey=mysqli_result($result,$i,"activity_id");
  		$mw=mysqli_result($result,$i,"missed_word");
  		//echo "$mykey and $mw<br>"; 
 
	}


}

if ($student_counter > 0)
{
$attempts = $student_counter;
$failures = $num; 
$percent = round((($student_counter - $failures)/$student_counter)*100); 

echo "You have assigned the word <b>$missed_word_escaped</b> across $num2 separate activities.<p>There have been $attempts attempts at this word with $failures mispronunciations.<p>This word is successfully pronounced $percent% of the time by your students.";

}

//What if we consider this word across all possible activities?

//Get info on all instructors from iSpraak GRADES table
$result = mysqli_query($msi_connect,"SELECT * FROM ispraak_stats WHERE missed_word='$missed_word' ORDER BY missed_word ASC");
$result2 = mysqli_query($msi_connect,"SELECT DISTINCT activity_id FROM ispraak_stats WHERE missed_word='$missed_word' ORDER BY missed_word ASC");
$num4=mysqli_num_rows($result);
$num5=mysqli_num_rows($result2);

//create a counter for student count
$student_counter2 = 0; 

//for each unique activity ID, check how many students have submitted something
for ($i = 0; $i < $num5; $i++)
	{
  
 	 	$mykey=mysqli_result($result2,$i,"activity_id");
 	 	$result3 = mysqli_query($msi_connect,"SELECT * FROM ispraak_grades WHERE activity_id='$mykey'");
		$num6=mysqli_num_rows($result3);
  		//echo "$mykey has $num3 students trying this word<br> "; 
 		$student_counter2 = ($student_counter2 + $num6); 
	}

if ($student_counter2 > 0)
{
$attempts = $student_counter2;
$failures = $num4; 
$percent = round((($student_counter2 - $failures)/$student_counter2)*100); 

echo "<p><i>Across all iSpraak data, this word has been assigned in $num5 activities, attempted $student_counter2 times, and mispronounced $failures times for a success rate of $percent%.</i>"; 
}


			
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



?>