<?php

/*

This page displays all grades for a given activity key pair (mykey and mykey2) and can be 
sorted by student name or by date completed. Displayed information includes name, email,
score, date completed, and number of mistakes. 

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

//Assume there is no error fetching grades
$error = ""; 

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
  	$error = "Unable to connect to database."; 
}

//Get mykey from query string & declare session variable or declare as NA
$mykey=$_GET['mykey'] ?? 'NA';
$mykey2=$_GET['mykey2'] ?? 'NA';
$sort=$_GET['sort'] ?? 'NA';


//Check iSpraak Table to confirm mykey1 and mykey2 are a secure pair
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey='$mykey' AND mykey2='$mykey2'");
$row = mysqli_fetch_array($myresult);
$rowcount=mysqli_num_rows($myresult);
$num = 0; 

if ($rowcount < 1)
{
  	$error = "Crikey! This page can't load. It is possible that you  have incorrectly entered the URL in the address bar above. Please confirm the link is not broken and reload the page."; 

}
else
{
	$error = "<span style=\"float:right\"><a href=\"grades.php?mykey=$mykey&mykey2=$mykey2&sort=name\" class=\"cutelink3\">Name</a> | <a href=\"grades.php?mykey=$mykey&mykey2=$mykey2&sort=date\" class=\"cutelink3\">Date</a></span><br><br>"; 

//allow for sorting by name or date (date is default)

if ($sort != "name")
{
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_grades where activity_id='$mykey' AND misc='$mykey2' ORDER BY timestamp DESC");
}
else
{
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_grades where activity_id='$mykey' AND misc='$mykey2' ORDER BY student_name ASC");
}

//confirm there are student grades for this particular actvity

$row = mysqli_fetch_array($myresult);
$num=mysqli_num_rows($myresult);
if ($num == 0) { $error = "Looks like you're in the right place, but there has been no student activity yet on this exercise. Please check back once students have submitted their work.";}

}


echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=25\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br>$error";

//for loop to display student grades

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

//correct some instances of UTF8 punctuation artefacts creating inaccurate number of missed words

if ($sscore == "100")
{
	$smissed = "0"; 
}

echo "<div class=\"tbl\">

<div class=\"col\"><div class=\"cell\">$readable_date</div></div>
<div class=\"col2\"><div class=\"cell\">$semail</div></div>
<div class=\"col\"><div class=\"cell\">$sscore%</div></div>
<div class=\"col\"><div class=\"cell\">$smissed mistakes</div></div>
</div><img src=\"images/top.png\" width=\"200\" height=\"7\">
<i><span style=\"color:gray\">$sname said: $seffort</i></span><br><br>"; 



$i++;
}

echo "<br><br></div></p></form>$ispraak_footer</div></body></html>";
			
//close your connection to the DB
mysqli_close($msi_connect);


?>