<?php

/*

Organized by student name and email, the roster page shows active students for activities made by the authenticated instructor. 
Displayed are activity creation date, model text, student completion date, and score. 
Activties that were completed more than once are indented and highlighted in blue. 
A unique activity count is also provided for an instructor to quickly determine how many assigned activities were completed. 
Instructor can filter between 48 hours and the default of 2 years on this page. 
Email address and Token are required for this page to display. 

*/

//start the session
session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//Get function from query string for this page
//Not every call to this page will need all these variables
$email=$_GET['email'];
$filter=$_GET['filter'] ?? 604800; //measured in seconds, defaults to one week, 63000000 is two years
$mykey=$_GET['mykey'];
$mykey2=$_GET['mykey2'];
$blocktext=$_GET['blocktext'];
$ispraak_token=$_GET['token'];
$permission = "denied"; 
$timerightnow = time();
$filter_time = ($timerightnow - $filter); 
$filter_time2 = date('m/d/y', $filter_time);


//putting in a login button to take them back to the login page
$login_button = "<center><a href=\"login.php?action=reset\" class=\"button5\">Login</a></center> "; 


//connect to database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

//Escape query string variables 

$ispraak_token = mysqli_real_escape_string($msi_connect, $ispraak_token);
$email = mysqli_real_escape_string($msi_connect, $email);


$auth_query = mysqli_query($msi_connect, "SELECT * FROM ispraak_auth where token='$ispraak_token' AND email='$email'");

$a = 0;
$auth_time=mysqli_result($auth_query,$a,"auth_time");
$auth_time_expire = $auth_time + 25200; 
$ispraak_time = time();

if ($auth_time_expire > $ispraak_time)
{
$permission = "good";
}

if ($permission == "good")
{

//define the query - QUERY for all UNIQUE student EMAILS associated with the instructor_email SORT by student name from ispraak_grades.
$myresult = mysqli_query($msi_connect, "SELECT DISTINCT * FROM ispraak_grades WHERE teacher_email='$email' AND student_email!='guest@ispraak.net' AND timestamp > '$filter_time' GROUP BY student_email ORDER BY student_name ASC");

$row = mysqli_fetch_array($myresult);
$num=mysqli_num_rows($myresult);

echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=17\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/centersimple.css?v=G\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/pace.js\"></script></head>

<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> <a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
		
<br><br><br><center>You have successfully authenticated ($email) <br>
<br></div>";

//echo "<center>Filter: None, 7 days, 30 days, 60 days, 120 days</center><p>"; 
echo "<center>Active students since $filter_time2:  <a href=\"roster.php?email=$email&token=$ispraak_token&filter=172800\" class=\"cutelink3\">48 hours</a> |  <a href=\"roster.php?email=$email&token=$ispraak_token&filter=604800\" class=\"cutelink3\">One Week</a> | <a href=\"roster.php?email=$email&token=$ispraak_token&filter=2592000\" class=\"cutelink3\">One Month</a> | <a href=\"roster.php?email=$email&token=$ispraak_token&filter=16000000\" class=\"cutelink3\">6 Months</a> | <a href=\"roster.php?email=$email&token=$ispraak_token&filter=63000000\" class=\"cutelink3\">All</a>
</center><p>";
echo"<hr class=\"alt\"><br><a href=\"roster_export.php?email=$email&token=$ispraak_token&filter=$filter\"target=\"_blank\" ><img src=\"images/csv.png\" align=\"right\" width=\"35\"></a>";

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

//echo"$readable_date";


echo"$sname (<a href=\"student_stats.php?email=$semail&id=$sname\" class=\"cutelink3\" target=\"_blank\">$semail</a>)";


//define query - Query from iSpraak_grades for all activities done by the particular student (student_email)
$query_distinct=mysqli_query($msi_connect, "SELECT DISTINCT activity_id FROM ispraak_grades where student_email='$semail' AND teacher_email='$email'  AND timestamp > '$filter_time' ");
$query2=mysqli_query($msi_connect, "SELECT activity_id, score, timestamp,misc FROM ispraak_grades where student_email='$semail' AND teacher_email='$email'  AND timestamp > '$filter_time' ORDER BY student_email ASC");

$row2 = mysqli_fetch_array($query2);
$num2 =mysqli_num_rows($query2);

$num_distinct=mysqli_num_rows($query_distinct);

echo" - $num_distinct unique activities completed!<br><br>";

//for identifying duplicates
$cached_activity_id = ".";

$j=0;

while($j < $num2){

	echo "<div class=\"tbl\">";

	$ssname=mysqli_result($query2,$j,"student_name");
	$ssemail=mysqli_result($query2,$j,"student_email");
	$activity=mysqli_result($query2,$j,"activity_id");
	$activity2=mysqli_result($query2,$j,"misc");
	$score1=mysqli_result($query2,$j,"score");
	$time1=mysqli_result($query2,$j,"timestamp");
	$readable_date1 = date('m/d/y', $time1);

	//define query - to display the blocktext for that particular student email
	$query2b=mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey='$activity' and mykey2 = '$activity2'");

	$array_blocktext = mysqli_fetch_array($query2b);
	$blocktext=mysqli_result($query2b,0,"blocktext",);
	//$newtext = substr($blocktext,0,40); 
	$newtext=mb_strcut($blocktext, 0, 40, "UTF-8");
	$date_created=mysqli_result($query2b,0,"mykey");
	$readable_date2 = date('m/d/y', $date_created);
	
	if ($cached_activity_id == $activity)
	{
		//$newtext = substr($blocktext,0,35); 
		$newtext=mb_strcut($blocktext, 0, 35, "UTF-8");

		$newtext = '<i><span style="background-color:#e6f7ff"> → '. $newtext . '</span></i>'; 
	}
	else
	{
		$cached_activity_id = $activity; 
	}

	echo "

	<div class=\"col\"><div class=\"cell\">$readable_date2</div></div>
	<div class=\"col2\"><div class=\"cell\">$newtext</div></div>
	<div class=\"col\"><div class=\"cell\">$readable_date1</div></div>
	<div class=\"col\"><div class=\"cell\">$score1%</div></div></div>
";
	
	$j++;





}
echo"<br><hr class=\"alt\">";



$i++;

}


if(isset($_POST["export"]))
{

	$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=export.csv');
	$output=fopen("php://output","w");
	fputcsv($output,array('Date Created','Text','Date Completed','Score'));
	while($rowing = mysqli_fetch_assoc($query2b));
	{
		fputcsv($output,$rowing);
	}
	fclose($output);

}


if ($num_distinct < 1)
{
	echo "<center><br><br>No activities to show for the selected time period.<br><br><br><br>"; 
}


	echo"</form>	
		$ispraak_footer
	</div>
	</body>
</html>

";

mysqli_close($msi_connect);

}

else{


echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=C\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br><center>Unable to authenticate your e-mail address.<br><br>Please press the login button to try again.<br><br>$login_button
<br>		</div>		
			</p><ul>";




			echo"</form>	
			$ispraak_footer
		</div>
		</body>
	</html>";





}




?>