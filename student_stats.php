<?php

/*

This page takes student email and displays a history of ALL missed words, all submitted
activities with scores, and a link to build a custom student set

Once this page is generated from roster.php, the URL is meant to be shared directly
with the student. 

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
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}

$email=$_GET['email'] ?? 'NA';
$sname=$_GET['id'] ?? 'NA';


$myresult = mysqli_query($msi_connect, "SELECT DISTINCT * FROM ispraak_stats where student_email='$email'");
$row = mysqli_fetch_array($myresult);
$rowcount=mysqli_num_rows($myresult);
$query_distinct=mysqli_query($msi_connect, "SELECT activity_id, score, timestamp,misc, effort_text FROM ispraak_grades where student_email='$email' ORDER BY activity_id DESC, timestamp DESC");
$num_distinct=mysqli_num_rows($query_distinct);
$row2 = mysqli_fetch_array($query_distinct);
$num2 =mysqli_num_rows($query_distinct);

if ($rowcount < 1)
{
  	$error = "Crikey! This page can't load. It is possible that you  have incorrectly entered the URL in the address bar above. Please confirm the link is not broken and reload the page."; 

}

echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=16\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 			

<br><br><br><center> Student Report for ($email) <br>
<br>		</div>		
			</p><ul>";


echo"<center> $email has completed $num_distinct unique activities!<br><br></center>";


$missed_words_message = "<center>You have no recently missed words.<br>"; 
if ($rowcount > 0) {
$missed_words_message = "Your missed words: <br><br><center>"; 
}

echo "$missed_words_message"; 

$i=0;
while ($i < $rowcount) 
{

$missed=mysqli_result($myresult,$i,"missed_word");

echo "<a href=\"https://forvo.com/search/$missed\" class=\"cutelink3\" target=\"_blank\">$missed</a> | ";
$i++;

}


echo"</center><br>";

echo"<hr class=\"alt\">";

echo "<center>Click <a href=\"student_custom_set.php?email=$email&id=$sname\" class=\"cutelink3\" target=\"_blank\">here</a> to build a custom review activity set for $email</center>";


echo"<hr class=\"alt\"><br>";


echo"Your completed activities are below with duplicates <span style=\"background-color:#e6f7ff\">highlighted in blue.</span> <br><br>";

echo "<div class=\"table1\">";
echo"<div class=\"column2\"><div class=\"cell12\"><u>Submission</u></div></div>
<div class=\"column1\"><div class=\"cell12\"><u>Completed</u></div></div>
<div class=\"column1\"><div class=\"cell12\"><u>Score</u></div></div>
<div class=\"column1\"><div class=\"cell12\"><u>Link</u></div></div>
</div>";

echo"<br>";


$j=0;

//keep track if an activity is a duplicate and how many duplicates there are

$cached_activity_id = ".";
$multiple_submits = "1";


while($j<$num2)
{

	echo "<div class=\"table1\">";
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
	}
	else
	{
		$cached_activity_id = $aid; 
		$multiple_submits = "1";
	}

	echo "
	<div class=\"column2\"><div class=\"cell12\">$effort_text</div></div>
	<div class=\"column1\"><div class=\"cell12\">$date_end</div></div>
	<div class=\"column1\"><div class=\"cell12\">$score</div></div>
	<div class=\"column1\"><div class=\"cell12\"><a href=\"$domain_name/ispraak.php?mykey=$aid&mykey2=$mykey2\" class=\"cutelink3\" target=\"_blank\">Link</a></div></div>
";

	
	$j++;

	echo"</div>";

}


echo "	</ul>		

		</form>	
		$ispraak_footer
	</div>

	</body>
</html>";
			
//close your connection to the DB
mysqli_close($msi_connect);

?>