<?php


//start the session
session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//Get function from query string for this page
//Not every call to this page will need all these variables
$email=$_GET['email'];
$filter=$_GET['filter'] ?? 63000000; //measured in seconds, defaults to two years 
$mykey=$_GET['mykey'];
$mykey2=$_GET['mykey2'];
$blocktext=$_GET['blocktext'];
$ispraak_token=$_GET['token'];
$permission = "denied"; 
$timerightnow = time();
$filter_time = ($timerightnow - $filter); 
$filter_time2 = date('m/d/y', $filter_time);

//connect to database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

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
$myresult = mysqli_query($msi_connect, "SELECT student_name, student_email, score, effort_text, missed_words FROM ispraak_grades WHERE teacher_email='$email' AND student_email!='guest@ispraak.net' AND timestamp > '$filter_time' ORDER BY timestamp ASC");


header('Content-Type: text/csv; charset=utf-8'); 
header('Content-Disposition: attachment; filename=ispraak_grades.csv');  
$output = fopen("php://output", "w"); 

fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));


while ($column = mysqli_fetch_field($myresult)) {
	$column_names[] = $column->name;
}

$column_names=array("Student Name","Student Email","Score","Submission","Missed Words");


// Write column names in csv file
if (!fputcsv($output, $column_names))
	die('Can\'t write column names in csv file');
  


// Get table rows

while ($row = mysqli_fetch_row($myresult)) {
	
	// Write table rows in csv files
	if (!fputcsv($output, $row))
		die('Can\'t write rows in csv file');
}



fclose($output);
}

else
{
	echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=C\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br><center>Unable to authenticate your request and generate your file.<br>
<br>		</div>		
			</p><ul>";




			echo"</form>	
			$ispraak_footer
		</div>
		</body>
	</html>";

}

?>

