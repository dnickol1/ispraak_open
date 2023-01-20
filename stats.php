<?php

/*

This page shows statistical frequency of missed words for any given
activity. Requires mykey, mykey2, and instructor e-mail address to load.
Missed words are ordered alphabetically with their total count, and the 
top 5 missed words (for most languages) appear in a bar graph in an iframe.

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


//Get variables from iSpraak GRADES table


$result = mysqli_query($msi_connect,"SELECT * FROM ispraak_stats WHERE activity_id='$mykey' AND misc='$mykey2' AND instructor_email='$email' ORDER BY missed_word ASC");
$num=mysqli_num_rows($result);

if ($num < 1)
{
  	$error = "<br>Sorry, there are no statistics available for this activity.<br><br>"; 
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

			<br><br><br>Statistical Error Frequency Count since $readable_date for $email </b> 
			
			<br></p>
						
		</div>						
			<ul >";
			
//Calculate some stats for teacher, but don't do for Chinese or Japanese
//ja or zh
//if ($a !== "ja" && $a !== "zh")

//want this stuff to float left so the iframe and stats graphics can float right

echo "<div style=\"float:left;width:300px\">";
echo "<SPAN STYLE=\"background-color: #E6E6E6\">Missed Words Stats</SPAN> / <a href=\"stats_progress.php?mykey=$mykey&mykey2=$mykey2&instructor_email=$email\" class=\"cutelink3\">Progress Stats</a> <br><br>"; 


$first_word = mysqli_result($result,0,"missed_word");
$stand_count = 0; 
$darray = array();


$resultC = mysqli_query($msi_connect,"SELECT * FROM ispraak_stats WHERE activity_id='$mykey' AND missed_word='$first_word'");
$numC=mysqli_num_rows($resultC);



//nicer display if possible
$equal_columns = ($num / 2);
$equal_columns = round($equal_columns);
echo "<div class=\"row\"><div class=\"column\">";





echo "$first_word ($numC) <br><br>"; 





for ($i = 0; $i < $num; $i++)
{
  
  $mw=mysqli_result($result,$i,"missed_word");
  
  //build up items in your array for the stats graph

  $darray[] = $mw;  
  
  if ($mw !== "$first_word")
  {
  	echo "$mw "; 
  	
  	
  	$resultB = mysqli_query($msi_connect,"SELECT * FROM ispraak_stats WHERE activity_id='$mykey' AND missed_word='$mw'");
	$numB=mysqli_num_rows($resultB);

  	echo "($numB) <br><br>"; 
  	
  	$stand_count = 1;
  	$first_word = $mw;
  }
  else
  {
    $stand_count++; 
  }
  
  if ($i == $equal_columns)
  {
  	echo "</div><div class=\"column\">";
  
  }
  
}

// end two column display
echo "</div></div>";

//end left float

echo "</div>";

//manual function to count the number of items in the array


//find frequency of items and put them in order
$counts = array_count_values($darray);
arsort($counts);



  // get the first key (the word)
  $temp1= array_keys($counts);
  $key1 = array_shift($temp1);
  

  // get the first value (the number)
  $temp_value=array_values($counts);
  $word1 = array_shift($temp_value);


  unset($counts["$key1"]) ;
  

//get second word and key woot

  // get the first key (the word)
  $temp2=array_keys($counts);
  $key2 = array_shift($temp2);
  

  // get the first value (the number)
  $temp_value2= array_values($counts);
  $word2 = array_shift($temp_value2);


  unset($counts["$key2"]) ;

//get third word and key woot

  // get the first key (the word)
  $temp3=array_keys($counts);
  $key3 = array_shift($temp3);
  

  // get the first value (the number)
  $temp_value3=array_values($counts);
  $word3 = array_shift($temp_value3);


  unset($counts["$key3"]) ;


//get fourth word and key woot

  // get the first key (the word)
  $temp4=array_keys($counts);
  $key4 = array_shift($temp4);
  

  // get the first value (the number)
  $temp_value4=array_values($counts);
  $word4 = array_shift($temp_value4);

  unset($counts["$key4"]) ;

  //get the fifth word and key woot

  $temp5=array_keys($counts);
  $key5 = array_shift($temp5);
  
  // get the first value (the number)
  $temp_value5 = array_values($counts);
  $word5 = array_shift($temp_value5);

  unset($counts["$key5"]) ;


//for certain languages, we need to turn off the graphing label
//since the characters will just show up as boxes
//this may be a non-issue if we dont allow acccess to this page
//for those languages

//this may also NOT be true with the NEWER version of JPGRAPH


$myresult2 = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey='$mykey' AND email='$email'");
$row2 = mysqli_fetch_array($myresult2);
$a2=$row2["language"];

if ($a2 != "ar" && $a2 != "he" && $a2 != "ur" && $a2 != "am" &&  $a2 != "bn" && $a2 != "zh" && $a2 != "hi" && $a2 != "ko" && $a2 != "ja" && $a2 != "th" && $a2 != "fa")
{
echo "<iframe frameborder=\"0\" height=\"300\" marginheight=\"0\" marginwidth=\"0\" scrolling=\"no\"
src=\"graphite_ispraak.php?a=$word1&b=$word2&c=$word3&d=$word4&e=$word5&w1=$key1&w2=$key2&w3=$key3&w4=$key4&w5=$key5\" width=\"300\"></iframe>";
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
}


?>