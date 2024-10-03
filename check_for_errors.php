<?php 

/*

This page runs in an iframe from review.php and is linked from asr_languages.html
The purpose of this page is to do all the analysis of the student submission as it 
compares to the instructor-provided model. Simplification of instructor and student
strings also occurs here in order to provide a better comparison between the two. 

Some additional language-specific features also appear here as IF conditions. 

Forvo.com links are generated for missed words. 

Student scores are saved into the DB from this page along with other stats. 

*/


//Starts or continues session from previous PHP page
session_start();

//Comment the below off to turn off error warnings
error_reporting(0);

//Get database variables and PHP functions

include_once("../../config_ispraak.php");

//all pages must display UTF-8 for any special chars

header('Content-type: text/html; charset=utf-8');
echo "<head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"> <link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\"><title>iSpraak Frame</title></head><body style=\"background-color:white\">";

//get whole shebang transcript, not just the halves

$ispraak_transcript= $_GET['transcript'];

//Check current PHP session to see if session variables are available

$block_text = $_SESSION['block_text'];
$student_name = $_SESSION['student_name'];
$iemail = $_SESSION['instructor_email'];
$student_email = $_SESSION['student_email'];
$activity_id_2 = $_SESSION['mykey2'];

//if the php session is destroyed for some reason, like inactivity or load balancing, use the cookies as a backup 

if (isset($_SESSION['student_email'])) 
{
	$unusedvariable = "999"; 
}
else
{

	$block_text = $_COOKIE["cookie_block_text"];
	$student_name = $_COOKIE["cookie_student_name"];  
	$iemail = $_COOKIE["cookie_instructor_email"];  
	$student_email = $_COOKIE["cookie_student_email"];  
	$activity_id = $_COOKIE["cookie_mykey"]; 
	$activity_id_2 = $_COOKIE["cookie_mykey2"]; 	
	$mylang = $_COOKIE["cookie_language"]; 

	//now reset into session variables again

	$_SESSION['mykey'] = $activity_id;
	$_SESSION['mykey2'] = $activity_id_2;
	$_SESSION['language'] = $mylang;
	$_SESSION['student_name'] = $student_name;
	$_SESSION['instructor_email'] = $iemail;
    $_SESSION['student_email'] = $student_email;
	
}

//If there is still a problem getting variable from session or cookies, report an error to student
//and save a record in DB to indicate the error and the attempt. 

if ($student_email == "") 
{
	echo "<br><br><center>Oops! There was an error saving your e-mail address. <br><br> Please relaunch this activity from the original link.<br><br>If you are blocking cookies or running an ad-blocker, please disable it now.<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>"; 	
	$student_email = "guest@ispraak.net";
	$iemail = "help@ispraak.net";
	$student_name = "Unknown";	
	$_SESSION['student_name'] = $student_name;
	$_SESSION['instructor_email'] = $iemail;
    $_SESSION['student_email'] = $student_email;

}

//The transcript may include slashes that need to be escaped

$result = $ispraak_transcript;
$result = stripslashes($result);

//This is the first output of this page and should be true for all languages

echo "I think you said: $result";

//For Chinese, we will provide transliterated text indicating unexpected words

$mylang = $_SESSION['language'];

if ($mylang == "zh")
{
	$student_pinyin = transliterator_transliterate('Any-Latin; Any-zh; Lower();', $result);
	$instructor_pinyin = transliterator_transliterate('Any-Latin; Any-Zh; Lower();', $block_text);
	find_mistakes($instructor_pinyin,$student_pinyin);
}

//We want see how similiar the model text is to the transcribed text

$good_text = $block_text;

//In order for a more accurate score, let's eliminate punctuation and case sensitivity based on the language

$str1 = remove_punctuation_and_lowercase($good_text, $mylang);
$str2 = remove_punctuation_and_lowercase($result, $mylang);

//We also want to address the different apostrophes ’ vs ' by normalizing them all to ' 

$str1 = apo_swap($str1);
$str2 = apo_swap($str2);

//Get a percentage calculated on similarity between two strings

similar_text("$str1", "$str2", $sim);
$sim=round($sim);    

//before displaying this score, see if there are wildcards. If wildcards are present, let's recalculate the score

$haystack = $good_text; 
$needle   = '**';
$wc = ''; 

if (strpos($haystack, $needle) !== false) 
{
	//there is at least ONE wildcard present
	//$sim = findWildCardScore($good_text,$result); 
	$sim = findWildCardScore($str1,$str2);

}



//now we have a score similarity either from STRICT or from wildcards
//we need to see if the instructor has a preference for flexible scoring

$flex_score_indicator = "";
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);
$flexible_scoring = "Strict"; 
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_user_prefs2 where email='$iemail' ORDER BY id DESC");
$rowcount=mysqli_num_rows($myresult);	
if ($rowcount > 0) { $flexible_scoring =mysqli_result($myresult,0,"pref_02"); }
if ($flexible_scoring == "Flexible")
{
	//instructor wants flexible scoring... now we check for the best existing score on this activity
	
	$flexkey1 =	$_SESSION['mykey'];
	$flexkey2 =	$_SESSION['mykey2'];
	
	//check to see if there is already a flexible text on record
	$my_flex_result = mysqli_query($msi_connect, "SELECT * FROM ispraak_flex where mykey = '$flexkey1' AND mykey2 = '$flexkey2' ORDER BY record DESC");
	$num_flexible_texts = $my_flex_result->num_rows;
	if ($num_flexible_texts > 0)
	{
		$top_text=mysqli_result($my_flex_result,0,"flex_text");
	}

	//now we determine if we replace the instructor MODEL text with the HIGHEST effort text
		
	$top_text = remove_punctuation_and_lowercase($top_text, $mylang);
	$str2 = remove_punctuation_and_lowercase($str2, $mylang);

	similar_text("$str2", "$top_text", $sim_flexible);
	$sim_flexible=round($sim_flexible);
		
	//echo "<br>Original score is $sim and flexible score is $sim_flexible and new text is: <br>$top_text<br>";
		
	if ($sim_flexible > $sim && $sim > 90)
		{
			$str1 = $top_text; 
			$sim = $sim_flexible; 
			$flex_score_indicator = "་";
			$flex_score_indicator = "<div class=\"tooltip\">꙳<span class=\"tooltiptext\" STYLE=\"font-size:x-small\">Flexible Scoring Applied</span></div> ";  				
		}   
		
}


//Display rounded score for similiarity of strings (wildcard scores are less precise and are word-based, not character-based)

echo "<br><h3>Score of $sim%$wc$flex_score_indicator </h3>";






//Display rounded score for similiarity of strings (wildcard scores are less precise and are word-based, not character-based)

//echo "<br><h3>Score of $sim% $wc</h3>";

//Connect language code to FORVO search codes

$forvo_code = "99"; 

if ($mylang == "fr") { $forvo_code = "fr"; }
if ($mylang == "hi") { $forvo_code = "hi"; }
if ($mylang == "es") { $forvo_code = "es"; }
if ($mylang == "de") { $forvo_code = "de"; }
if ($mylang == "en") { $forvo_code = "en"; }
if ($mylang == "it") { $forvo_code = "it"; }
if ($mylang == "ja") { $forvo_code = "ja"; }
if ($mylang == "pt") { $forvo_code = "pt"; }
if ($mylang == "ru") { $forvo_code = "ru"; }
if ($mylang == "ko") { $forvo_code = "ko"; }
if ($mylang == "hr") { $forvo_code = "hr"; }
if ($mylang == "zh") { $forvo_code = "zh"; }
if ($mylang == "ar") { $forvo_code = "ar"; }
if ($mylang == "el") { $forvo_code = "el"; }
if ($mylang == "vi") { $forvo_code = "vi"; }
if ($mylang == "tr") { $forvo_code = "tr"; }
if ($mylang == "nl") { $forvo_code = "nl"; }
if ($mylang == "ca") { $forvo_code = "ca"; }
if ($mylang == "cs") { $forvo_code = "cs"; }
if ($mylang == "sv") { $forvo_code = "sv"; }
if ($mylang == "he") { $forvo_code = "he"; }
if ($mylang == "pl") { $forvo_code = "pl"; }
if ($mylang == "zu") { $forvo_code = "zu"; }
if ($mylang == "sw") { $forvo_code = "sw"; }
if ($mylang == "am") { $forvo_code = "am"; }
if ($mylang == "fa") { $forvo_code = "fa"; }
if ($mylang == "no") { $forvo_code = "no"; }
if ($mylang == "da") { $forvo_code = "da"; }
if ($mylang == "fi") { $forvo_code = "fi"; }
if ($mylang == "hu") { $forvo_code = "hu"; }
if ($mylang == "uk") { $forvo_code = "uk"; }
if ($mylang == "ur") { $forvo_code = "ur"; }
if ($mylang == "ro") { $forvo_code = "ro"; }
if ($mylang == "id") { $forvo_code = "ind"; }
if ($mylang == "bn") { $forvo_code = "bn"; }
if ($mylang == "th") { $forvo_code = "th"; }


//Thai language is scriptio continua so we will speculate where spaces should be and then insert them

if ($mylang == "th")
{

	//First insert spaces into the MODEL text
	$no_spaces = $block_text;
	$add_spaces = transliterator_transliterate('Any-Latin; Thai-Latin; Lower();', $no_spaces);
	$add_underscores = str_replace(' ', '_', $add_spaces);
	$original_with_underscores =transliterator_transliterate('Thai-Latin; Latin-Thai; Lower();', $add_underscores);
	$original_with_spaces = str_replace('_', ' ', $original_with_underscores);
	$block_text = $original_with_spaces;
	$goodtext = $block_text;

	//Next insert spaces into the STUDENT text
	$no_spaces = $result;
	$add_spaces = transliterator_transliterate('Any-Latin; Thai-Latin; Lower();', $no_spaces);
	$add_underscores = str_replace(' ', '_', $add_spaces);
	$original_with_underscores =transliterator_transliterate('Thai-Latin; Latin-Thai; Lower();', $add_underscores);
	$original_with_spaces = str_replace('_', ' ', $original_with_underscores);
	$block_text_student = $original_with_spaces;
	$result = $block_text_student;

}

//Chinese and Japanese are currently separated by unicode character rather than word

if ($mylang == "zh" || $mylang == "ja")
{

	$str = $result;
	$str = $str2;

	$split=1; 
	
    $funarray = array();
     
    for ( $i=0; $i < strlen( $str ); )
    { 
        $value = ord($str[$i]); 
    
        if($value > 127)
        { 
            if($value >= 192 && $value <= 223) 
                $split=2; 
            elseif($value >= 224 && $value <= 239) 
                $split=3; 
            elseif($value >= 240 && $value <= 247) 
                $split=4; 
        }
        else
        { 
            $split=1; 
        } 
            $key = NULL; 
        
        	for ( $j = 0; $j < $split; $j++, $i++ ) 
        	{ 
            $key .= $str[$i]; 
        	}
        	 
        array_push( $funarray, $key ); 
    } 

//so funarray becomes effort_words array
//now do same thing for MODEL text, call it nofunarray for model_words array

	$str = $good_text;
	$str = $str1;

	$split=1; 
	
    $nofunarray = array();
     
    for ( $i=0; $i < strlen( $str ); )
    { 
        $value = ord($str[$i]); 
    
        if($value > 127)
        { 
            if($value >= 192 && $value <= 223) 
                $split=2; 
            elseif($value >= 224 && $value <= 239) 
                $split=3; 
            elseif($value >= 240 && $value <= 247) 
                $split=4; 
        }
        else
        { 
            $split=1; 
        } 
            $key = NULL; 
        
        	for ( $j = 0; $j < $split; $j++, $i++ ) 
        	{ 
            $key .= $str[$i]; 
        	}
        	 
        array_push( $nofunarray, $key ); 
    } 

$model_words = $nofunarray;
$effort_words = $funarray;

}
else
{

//Make arrays out of the simplified instructor (str1) and student (str2) text

$model_words = preg_split('/\s+/', $str1);
$effort_words = preg_split('/\s+/', $str2);

}

//Identify the missing words or charachters and create a new array to hold them

$missing_words = array_diff($model_words, $effort_words);

//re-indexes the array pointers since they are likely to be non-sequential at this point

$missing_words = array_values(array_filter($missing_words));

//connect to the Database

$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later!";
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}


if ($sim < 100)
{
	$num_missing_words = count($missing_words);

	if ($num_missing_words > 0) 
	{
		echo "<br>Review pronunciation of missed words at <a href=\"https://www.forvo.com\" class=\"cutelink3\" target=\"_blank\">Forvo.com</a></b><br><br>"; 
	}


	for ($i = 0; $i < count($missing_words); $i++)
	{
   
   		$thisword = $missing_words[$i];
   
		//do not output the wildcards if they are present here
	
		$wildcard_present = "no";
	
		if (strpos($thisword, '**') !== false) 
		{
			$wildcard_present = "yes";
		}


   		if ($thisword != "" && $wildcard_present == "no")
   		{
   			echo "[<a href=\"http://www.forvo.com/search-$forvo_code/$thisword\" class=\"cutelink\" target=\"_blank\">$thisword</a>] ";
     
     		//check for French homophones
     		if ($mylang == "fr")
     		{
     			$check_fr_homophone = confirm_mistake($thisword, $str2); 
     			if ($check_fr_homophone == true) { 
     				//echo "<img src=\"images/flag_fr_homophone.png\" width=\"15\">"; 
     				
     				echo "<div class=\"tooltip\"><img src=\"images/flag_fr_homophone.png\" class=\"smallicons\" height=\"15px\"  id=\"$thisword\"></a><span class=\"tooltiptext\">You said <i>$thisword</i> correctly! This is a French homophone!</span></div> "; 
     				
     				} 
	 			
     		}
   			//for each bad word, throw it into the DB under ispraak_stats
   			
   			//words with apostrophes not inserting and crashign under php 8.0
   			$thisword = mysqli_real_escape_string($msi_connect, $thisword);
   	
   			$activity_id9 = $_SESSION['mykey'];
   			$misc9 = $activity_id_2; 
   			$query88 = "INSERT INTO ispraak_stats VALUES ('$activity_id9','$iemail','$student_email','$thisword','$misc9','')";
			
			//execute the query
			mysqli_query($msi_connect, $query88);
		}
   
	}

}

//Congratulate the student if score is equal to or higher than the best score in the class

$top_score = 0; 
$activity_id9 = $_SESSION['mykey'];
$result2020 = mysqli_query($msi_connect, "SELECT * FROM ispraak_grades WHERE activity_id='$activity_id9' ORDER BY score DESC");
//$row2020 = mysqli_fetch_array($myresult2020);
$num2020 = $result2020->num_rows;
$i2 = 0; 
$top_score=mysqli_result($result2020,$i2,"score");

$temail_hide = hide_email($iemail);
 
$praise = "<br><br>Your score has been saved and sent to $temail_hide<br><br>"; 

if ($top_score <= $sim)
{
	$praise = "<p><center><img src=\"images/topscore.png\" align=\"center\" width=\"80\"><h2>You have the top score in this class!</h2>";
}

echo "$praise"; 


$praise2 = ReturnMessage($mylang, $sim);
echo "<h2>$praise2</h2>"; 



//Update the DIV tag for activityset if this is a set

$active_set = $_COOKIE["active_set"] ?? 'NA';
if ($active_set != "NA")
{
	$array1 = $_COOKIE["array1"];
	$array1 = json_decode($_COOKIE['array1'], true);
	$a1_count=count($array1);
}

if ($active_set == "true")
{
	echo "<script> parent.document.getElementById('activityset_next').style.display=\"inline\"; </script>";

	if ($a1_count > 0)
	{
	
		echo "<script> parent.document.getElementById('goForward').style.display=\"inline\"; </script>";	
	}
	
		else
	{
			echo "<script> parent.document.getElementById('main_body').style.background = \"#b3bec4 url('images/end_of_set_background.gif') repeat right top\";</script>";	

	}
	
}


//Save this students score into the database

$misc = "undefined"; 
$misc = $activity_id_2;
$student_name = $_SESSION['student_name'];
$teacher_email = $_SESSION['instructor_email'];
$student_email = $_SESSION['student_email'];
$score = $sim; 
$effort = $result;
$activity_id = $_SESSION['mykey'];
$timestamp = time();
$missed_words = count($missing_words);

//connect to the DB

$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}

//Slashes have already been escaped, but we need to further prepare strings for mySQLi insertion

$effort = mysqli_real_escape_string($msi_connect, $effort);
$student_name = mysqli_real_escape_string($msi_connect, $student_name);

//Define query

$query = "INSERT INTO ispraak_grades VALUES ('$student_name','$student_email','$score','$effort','$activity_id','$teacher_email','$timestamp','$missed_words','$misc', '')";

//execute the query

mysqli_query($msi_connect, $query)  or die(mysqli_error()."<br>iSpraak error saving results to database! Please report to dnickol1@slu.edu immediately! ");

//check if this is an LMS request and update the LTI table if needed 

$context_id = $_COOKIE["context_id"];

if (strlen($context_id) > 3)
{
	$lti_message = "YES";
	$role = "student"; 
	$misc999 = "999"; 
	$query2 = "INSERT INTO ispraak_lti VALUES ('$context_id', '$activity_id', '$student_email', '$role','$misc999','$misc999','$timestamp','')";
	mysqli_query($msi_connect, $query2);
}

//close your connection to the DB

mysqli_close($msi_connect);


?>