<?php

/*

This page allows instructors to create and manage SETS of activities that are tied together.
Activities in a set must share a common language of instruction. 
These sets are either private or public. Public sets are shared with anyone exploring sets. 
This page requires the action variable, instructor's email, and the ispraak token to load. 

*/

session_start();

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");


//Get function from query string for this page
//Not every call to this page will need all these variables

$action=$_GET['action'];
$email=$_GET['email'];
$manage_set=$_GET['set_id'];
$mykey=$_GET['mykey'];
$mykey2=$_GET['mykey2'];
$shared=$_GET['shared'];
$set_name=$_GET['set_name'];
$language=$_GET['language'];
$set_id=$_GET['set_id'];
$ispraak_token=$_GET['token'];
$permission = "denied"; 

//Is access allowed to this page - check for email and token pair and expiry

//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_auth where token='$ispraak_token' AND email='$email'");
$j = 0;
$auth_time=mysqli_result($myresult,$j,"auth_time");
$auth_time_expire = $auth_time + 25200; 
$ispraak_time = time();

if ($auth_time_expire > $ispraak_time)
{
$permission = "good";
}

if ($permission == "good")
{


if ($action == "add")
{

//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	$error_saving_db = "<p style=\"color:red\">Database connection: X</span>";
  	$vcode = "bad";
}
else
{
//since there is no connection error, sanitize all the user input
$set_name = mysqli_real_escape_string($msi_connect, $set_name);
$language = mysqli_real_escape_string($msi_connect, $language);
$shareable = mysqli_real_escape_string($msi_connect, $shared);
$email = mysqli_real_escape_string($msi_connect, $email);

}

//define the query
$query = "INSERT INTO ispraak_sets VALUES ('$set_name', '$email', '$language', '$mykey', '$mykey2','$shareable', '')";

//execute the query
//determine if it was a good insert
$good_insert = mysqli_query($msi_connect, $query);

$action = "manage"; 

//end bracket for adding activity to set

}





if ($action == "remove")
{

//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	$error_saving_db = "<p style=\"color:red\">Database connection: X</span>";
  	$vcode = "bad";
}
else
{
//since there is no connection error, sanitize all the user input
$set_name = mysqli_real_escape_string($msi_connect, $set_name);
$language = mysqli_real_escape_string($msi_connect, $language);
$shareable = mysqli_real_escape_string($msi_connect, $shared);
$email = mysqli_real_escape_string($msi_connect, $email);

}

//define the query
$query = "UPDATE ispraak_sets SET mykey = 'empty' WHERE mykey = '$mykey' AND email = '$email' AND set_name = '$set_name' "; 

//execute the query
//determine if it was a good insert
$good_insert = mysqli_query($msi_connect, $query);

$action = "manage"; 

//end bracket for removing activity to set

}



if ($action == "trash")
{

//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	$error_saving_db = "<p style=\"color:red\">Database connection: X</span>";
  	$vcode = "bad";

}
else
{
//since there is no connection error, sanitize all the user input
$set_name = mysqli_real_escape_string($msi_connect, $set_name);
$language = mysqli_real_escape_string($msi_connect, $language);
$shareable = mysqli_real_escape_string($msi_connect, $shared);
$email = mysqli_real_escape_string($msi_connect, $email);
$set_id = mysqli_real_escape_string($msi_connect, $set_id);

}

//define the queries
$query = "UPDATE ispraak_sets SET shared = 'deleted' WHERE set_name = '$set_name' AND email = '$email'"; 
$query2 = "UPDATE ispraak_sets SET email = 'empty' WHERE set_name = '$set_name' AND email = '$email'"; 

//execute the query
//determine if it was a good insert
$good_insert = mysqli_query($msi_connect, $query);
$good_insert2 = mysqli_query($msi_connect, $query2);

$action = "view"; 

//end bracket for trashing set

}










if ($action == "view" && $email != "")
{
//this page should show information to instructor on creating a new set
//https://www.ispraak.net/sets.php?action=view&email=student@college.edu

echo "$ispraak_header
	
		<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
			
		$ispraak_logo
		<a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
		<br><br><br>
		<center>Activity Sets for $email</center><br><br>";
		
		
//show all existing sets inside this form

//Connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);
//Only do UNIQUE set names?
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_sets where email='$email' GROUP BY set_name");
$rowcount=mysqli_num_rows($myresult);	

if ($rowcount < 1)
{
	echo "<center>No activity sets found. <br><br><a href=\"sets.php?action=create&email=$email&token=$ispraak_token\" class=\"button5\">Create New</a></center><br><br>"; 
}
else
{
	echo "<a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\">Go Back</a> | <a href=\"sets.php?action=create&email=$email&token=$ispraak_token\" class=\"cutelink3\">Create New Set</a></center><br><br>";
	echo "Click on the set below to manage activities: <br><br>";
	
}
	
		
$i=0;
while ($i < $rowcount) 
{

$set_name=mysqli_result($myresult,$i,"set_name");
$set_id=mysqli_result($myresult,$i,"set_id");
$language=mysqli_result($myresult,$i,"language");
$shared=mysqli_result($myresult,$i,"shared");

$lang_desc = outputLanguage($language);

echo "<li><a href=\"sets.php?action=manage&email=$email&set_id=$set_id&token=$ispraak_token&language=$language\" class=\"cutelink3\">$set_name</a> ($lang_desc) $shared</li>";

$i++;

}


//close your connection to the DB
mysqli_close($msi_connect);

		
		
		
		
		
echo"			
			
		</form>	
		$ispraak_footer
	</div>
	</body>
	
</html>";


//above this bracket user is viewing
}







if ($action == "manage" && $email != "")
{
//this page should show information to instructor on creating a new set
//https://www.ispraak.net/sets.php?action=view&email=student@college.edu

echo "$ispraak_header
	
		<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
			
		$ispraak_logo
		
		
			<a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
	
		
		<br><br><br><br>";
		
		
//show all existing sets inside this form

//Connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);
//Only do UNIQUE set names?
$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_sets where set_id='$manage_set'");
$rowcount=mysqli_num_rows($myresult);	

if ($rowcount < 1)
{
	echo "<center>No activity sets found. <a href=\"sets.php?action=create&email=$email&token=$ispraak_token\">Create New</a></center>"; 
}
else
{

	$set_name=mysqli_result($myresult,0,"set_name");
	$lang=mysqli_result($myresult,0,"language");
	$shared=mysqli_result($myresult,0,"shared");
	$set_id=mysqli_result($myresult,0,"set_id");
	

	echo "<a href=\"sets.php?action=trash&token=$ispraak_token&email=$email&set_id=$set_id&set_name=$set_name\" class=\"cutelink4\"><img src = \"/images/trash_it.png\" width=\"30\" align=\"right\"></a>";
	echo "<a href=\"sets.php?action=view&token=$ispraak_token&email=$email\" class=\"cutelink3\">← Go Back</a> | <a href=\"sets_students.php?id=$set_id\" class=\"cutelink3\" target=\"_blank\">Student Link</a> | <a href=\"sets_results.php?id=$set_id&token=$ispraak_token&email=$email&language=$language\" class=\"cutelink3\">Results</a><br><br> ";		
	echo "Click on your iSpraak activities to be included in <strong>$set_name:</strong><br><br>";
	
}



//now show links for activities made with this email address

$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak where email='$email' AND language='$language' ORDER BY mykey DESC");
$num=mysqli_num_rows($myresult);		

if ($num < 1)
{
	echo "<br><br><center>There are no activities available in this language.</center><br><br>";
}	

$i=0;
while ($i < $num) 
{

$j = $i+1;
$key1=mysqli_result($myresult,$i,"mykey");
$key2=mysqli_result($myresult,$i,"mykey2");
$key3=mysqli_result($myresult,$i,"blocktext");
$key4=mb_strcut($key3, 0, 42, "UTF-8");

$key5 = $key4 . ""; 
$key6=date('m/d/Y', $key1);
$key7=mysqli_result($myresult,$i,"language");


$myresult2 = mysqli_query($msi_connect, "SELECT * FROM ispraak_sets where mykey='$key1' AND mykey2='$key2' AND set_name='$set_name' AND email='$email'");

if (!$myresult2) { 
$num2 = 0; 
}
else {
$num2=mysqli_num_rows($myresult2);	
}




if ($num2 < 1)
{
$key7="<a href=\"sets.php?action=add&mykey=$key1&mykey2=$key2&email=$email&shared=$shared&set_name=$set_name&set_id=$set_id&language=$key7&token=$ispraak_token\" class=\"cutelink3\">Add → </a>";
}
else
{
$key7="<a href=\"sets.php?action=remove&mykey=$key1&mykey2=$key2&email=$email&shared=$shared&set_name=$set_name&set_id=$set_id&language=$key7&token=$ispraak_token\" class=\"cutelink3\">Remove</a>";
}


echo "<li>$key7 | $key6 | $key5</li>"; 

$i++;
}
	




//close your connection to the DB
mysqli_close($msi_connect);

		
		
		
		
		
echo"			
			
		</form>	
		$ispraak_footer
	</div>
	</body>
	
</html>";


//above this bracket user is viewing
}

if ($action == "create" && $email != "")
{
//this page should show information to instructor on creating a new set
//https://www.ispraak.net/sets.php?action=create&email=student@college.edu

echo "$ispraak_header
	
		<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"sets.php?action=insert&email=$email&token=$ispraak_token\">
			
		$ispraak_logo
		
					<a href=\"login.php?token=$ispraak_token&email=$email\" class=\"cutelink3\"><img src = \"images/gohome.png\" align=\"right\" width=\"40\"></a>			
	
		
		<br><br><br>
					
			
				<ul >
			
					<li id=\"li_1\" >
		<label class=\"description\" for=\"student_name\">What do you want to name this set?</label>
		<div>
			<input id=\"set_name\" name=\"set_name\" maxlength=\"25\" class=\"element text medium\" type=\"text\"/> 
		</div><p class=\"guidelines\" id=\"guide_1\">Examples: <i>Turkish Greetings</i>, <i>Medical Spanish</i>, or <i>French Slang</i></p> 
			
		</li>		
				
		<p hidden class=\"guidelines\" id=\"guide_1\"></p>
	
		
		
				<li id=\"li_3\" >
		<label class=\"description\" for=\"element_3\">Language of Instruction </label>
		<div>
		<select class=\"element select medium\" id=\"element_3\" name=\"element_3\"> 
<option value=\"am\" >Amharic</option>
<option value=\"ar\" >Arabic</option>
<option value=\"bn\" >Bengali</option>
<option value=\"ca\" >Catalan</option>
<option value=\"zh\" >Chinese</option>
<option value=\"hr\" >Croatian</option>
<option value=\"cs\" >Czech</option>
<option value=\"da\" >Danish</option>
<option value=\"nl\" >Dutch</option>
<option value=\"en\" selected=\"selected\">English</option>
<option value=\"fi\" >Finnish</option>
<option value=\"fr\" >French</option>
<option value=\"de\" >German</option>
<option value=\"el\" >Greek</option>
<option value=\"he\" >Hebrew</option>
<option value=\"hi\" >Hindi</option>
<option value=\"hu\" >Hungarian</option>
<option value=\"id\" >Indonesian</option>
<option value=\"it\" >Italian</option>
<option value=\"ja\" >Japanese</option>
<option value=\"ko\" >Korean</option>
<option value=\"no\" >Norwegian</option>
<option value=\"fa\" >Persian</option>
<option value=\"pl\" >Polish</option>
<option value=\"pt\" >Portuguese</option>
<option value=\"ro\" >Romanian</option>
<option value=\"ru\" >Russian</option>
<option value=\"es\" >Spanish</option>
<option value=\"sw\" >Swahili</option>
<option value=\"sv\" >Swedish</option>
<option value=\"th\" >Thai</option>
<option value=\"tr\" >Turkish</option>
<option value=\"uk\" >Ukrainian</option>
<option value=\"ur\" >Urdu</option>
<option value=\"vi\" >Vietnamese</option>
<option value=\"zu\" >Zulu</option>

		</select><p class=\"guidelines\" id=\"guide_3\">Select language for this set! </p>
		</div> 
		</li>	
		
		
		<li id=\"li_4\" >
		<label class=\"description\" for=\"element_4\">Add this set to the public directory?</label>
		<span>
			<input id=\"element_4_1\" name=\"element_4\" class=\"element radio\" type=\"radio\" value=\"private\" checked=\"checked\" />
<label class=\"choice\" for=\"element_4_1\">Keep private</label>
<input id=\"element_4_2\" name=\"element_4\" class=\"element radio\" type=\"radio\" value=\"public\"/>
<label class=\"choice\" for=\"element_4_2\">Share publicly</label>
</span><p class=\"guidelines\" id=\"guide_4\">Public sets can be found by any iSpraak user! Private sets can be shared with your students.</p> 
		</li>			
					<li class=\"buttons\">
			    <input type=\"hidden\" name=\"form_id\" value=\"1007732\" />
				    <input type=\"hidden\" name=\"email\" id=\"email\" value=\"$email\" />
			       <input type=\"hidden\" name=\"token\" id=\"token\" value=\"$ispraak_token\" />
			    
				<input id=\"saveForm\" class=\"button5\" type=\"submit\" name=\"submit\" value=\"Create Set\" />
		</li>
			</ul>

	
			</p>

		</form>	
		$ispraak_footer
	</div>
	</body>
	
</html>";


//above this bracket user is making an activity
}

if ($action == "insert")
{
//get all the post variables from the form and prepare them for the database insertion

$set_name=$_POST['set_name'];
$language=$_POST['element_3'];
$shareable=$_POST['element_4'];
$email=$_POST['email'];

//Substitute reserved apostrophe with a directional one to fix add and remove bug
//and substitute ampersand with an alternative one
$set_name = str_replace("'","’",$set_name); 
$set_name = str_replace("&","＆",$set_name); 
$set_name = str_replace("\"","-",$set_name); 


//connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

if (mysqli_connect_errno())
{
  	$error_saving_db = "<p style=\"color:red\">Database connection: X</span>";
  	$vcode = "bad";
}
else
{
//since there is no connection error, sanitize all the user input
$set_name = mysqli_real_escape_string($msi_connect, $set_name);
$language = mysqli_real_escape_string($msi_connect, $language);
$shareable = mysqli_real_escape_string($msi_connect, $shareable);
$email = mysqli_real_escape_string($msi_connect, $email);

}

$mykey = "empty";
$mykey2 = "empty";

if($set_name != strip_tags($set_name))
{

}
else
{

//define the query
$query = "INSERT INTO ispraak_sets VALUES ('$set_name', '$email', '$language', '$mykey', '$mykey2','$shareable', '')";

//execute the query
//determine if it was a good insert
$good_insert = mysqli_query($msi_connect, $query);


}



//new monster IF statement to avoid duplicate mykey issue
//or any other INSERT problem

if (!$good_insert)
{
//new activity was not inserted

	echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br>
<p>
<center>Sorry, there was an issue with your set name. Press BACK to try again.<br><br><br>

<br>		</div>		
			</p>

		</form>	
		$ispraak_footer
	</div>
	</body>
</html>";
	
	
	
	
	

	
	
	
	
	
	
	
	


}
else
{

//Confirmation that record was inserted, now display all instructor sets?

	$newURL = "sets.php?action=view&email=$email&token=$ispraak_token";
	header('Location: '.$newURL);

}

//close your connection to the DB
mysqli_close($msi_connect);



//above this bracket is the insert function
}

//above this bracket user is authenticated
}



else
{

echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br><center>Sorry, we are unable to authenticate you right now.<br><br><br>
<a href=\"login.php\" class=\"button4\">Login Page</a>
<br>
<br>		</div>		
			</p>

		</form>	
		$ispraak_footer
	</div>
	</body>
</html>";
}


?>

