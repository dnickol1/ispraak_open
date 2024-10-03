<?php

/*

This page provides instructors with an overview of activities completed on sets they've created. 
Displays number of activities completed by a given student, their averaged score across
multiple activities, and the average number of mistakes per activity completed. 

Page requires authentication to load (email, token) and language and unique set ID number. 

*/

//Continue PHP session from prior page 
session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//Get all needed variables from the query string 
$set_id=$_GET['id'];
$ispraak_token=$_GET['token'];
$email=$_GET['email'];
$language=$_GET['language'];

//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	$error_saving_db = "<p style=\"color:red\">Database connection: X</span>";
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
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
	
	$teacher_email = $set_email;
	$all_ids=$a1;
	$num_of_activities_in_this_set=$rowcount_2;

	//Get unique group of students for this instructor
	$myresult = mysqli_query($msi_connect, "SELECT DISTINCT student_email FROM ispraak_grades where teacher_email='$teacher_email' ORDER BY timestamp DESC");
	$num_of_students=mysqli_num_rows($myresult);
	$count_of_activities_in_set_done = 0; 

	echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\">
<a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
	


<br><br><br>

<br><a href=\"sets.php?action=manage&email=$email&token=$ispraak_token&language=$language&set_id=$set_id\" class=\"cutelink3\">Go Back</a>

<br><center>Here are the <strong>averaged</strong> results for your set *$final_name*<p></center></div><div> ";



echo "<div class=\"tbl\"><div class=\"col\"><div class=\"cell\">Completed</div></div><div class=\"col2\"><div class=\"cell\">Student</div></div>
<div class=\"col\"><div class=\"cell\">Score</div></div><div class=\"col\">Mistakes</div></div><br>";


$i=0;
while ($i < $num_of_students) 
{

	//$sname=mysqli_result($myresult,$i,"student_name");
	$semail=mysqli_result($myresult,$i,"student_email");

	//$my_next_result = mysqli_query($msi_connect, "SELECT DISTINCT activity_id FROM ispraak_grades where student_email='$semail'");
	$my_next_result = mysqli_query($msi_connect, "SELECT *  FROM ispraak_grades where student_email='$semail' group by activity_id");
	
	$num_of_activities=mysqli_num_rows($my_next_result);
	
	$total_score = 0; 
	$total_missed_words = 0; 

	$j = 0;
	while ($j < $num_of_activities)
	{
	
		//DOES THIS PARTICULAR ACTIVITY PART OF THE SET?
		
		$said=mysqli_result($my_next_result,$j,"activity_id");
		$score=mysqli_result($my_next_result,$j,"score");
		$mw=mysqli_result($my_next_result,$j,"missed_words");
		
	
		if(strpos($all_ids, $said) !== false)
		{
  			$count_of_activities_in_set_done++;
  			$total_score = ($score + $total_score); 
  			$total_missed_words = ($mw + $total_missed_words);
  			//echo "$said, $total_score, $total_missed_words. "; 
		}
	
	$j++; 
	}

if ($count_of_activities_in_set_done > 1)
{
$average_score = (($total_score / $count_of_activities_in_set_done)); 
$average_score = round($average_score);

$average_mw = (($total_missed_words / $count_of_activities_in_set_done)); 
$average_mw = round($average_mw);


echo "<div class=\"tbl\"><div class=\"col\"><div class=\"cell\">$count_of_activities_in_set_done of $num_of_activities_in_this_set</div></div><div class=\"col2\"><div class=\"cell\">$semail</div></div>


<div class=\"col\"><div class=\"cell\">$average_score%</div></div>

<div class=\"col\">~$average_mw</div></div>";
}

$count_of_activities_in_set_done = 0; 

$i++;
}

echo "
	</div>		
			</p>

		</form>	
		$ispraak_footer
	</div>
	
	</body>
</html>";
	
	
	
}

//close your connection to the DB
mysqli_close($msi_connect);






?>

