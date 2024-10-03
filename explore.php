<?php

include_once("../../config_ispraak.php");


/*

This page displays iSpraak activity sets that have been marked as public. 
Students first select their language and can then browse by topic. 
No authentication is required. 

*/

//Check to see if the language is set from query string
//If it is not set, show the language selection form below

$language=$_POST['language'] ?? 'NA';

if ($language == "NA")
{

echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=16\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"explore.php\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br><center>Select a language below to explore iSpraak shared activity sets.<br><br><br>

<select id = 'language' name= 'language' style = 'position: relative'>

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
<option value=\"99\" >Show all languages</option>

</select>
</form><br><br><br>

<center><input id\"saveForm\" class=\"button4\" type=\"submit\" name=\"submit\" value=\"Submit\" /></center>

<br>
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
//all that code from publicset 

//connection to database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

//query
$language=$_POST['language'];
$result = mysqli_query($msi_connect,"SELECT * FROM ispraak_sets where language='$language' AND shared='public'  GROUP BY set_name");
$rowcount=mysqli_num_rows($result);	

if ($language == "99")
{
$result = mysqli_query($msi_connect,"SELECT * FROM ispraak_sets where shared='public'  GROUP BY set_name");
$rowcount=mysqli_num_rows($result);	
}


if ($rowcount <1)

{
	echo "$ispraak_header
	
		<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"publicset.php\">
			
		$ispraak_logo<br><br><br>
	     
		<center>Sorry! No public activity sets found for this language!<br><br><br><br><div>";


}
else{

echo "$ispraak_header
	
		<form id=\"form_language\" class=\"ispraak_form\"  method=\"post\" action=\"publicset.php\">
			
		$ispraak_logo<br><br><br>
					
		<center>Click an activity set below to begin practicing. Starred sets are our favorites!</center> <br>";

//show all existing sets inside this form


echo "<div class=\"public_set\">";


$i=0;
while ($i < $rowcount) 
{

$rowcount=mysqli_num_rows($result);	
$set_name=mysqli_result($result,$i,"set_name");
$set_id=mysqli_result($result,$i,"set_id");
$set_email=mysqli_result($result,$i,"email");

//lets highlight anything made by llc@slu.edu 
$starred_set = ""; 

if ($set_email == "llc@slu.edu")
{
	$starred_set = "★"; 
}

//Should look like this: https://www.ispraak.net/sets_students.php?id=75

//new query to display the number of activities in this particular set
$myresult2 = mysqli_query($msi_connect, "SELECT * FROM ispraak_sets where email='$set_email' AND set_name='$set_name' AND mykey!='empty'");
$num_sets=mysqli_num_rows($myresult2);	

//here, we have $num_sets > 1 because there is an empty set already. 
if($num_sets>1)
{

echo "<a class=\"button6\" href=\"sets_students.php?id=$set_id\" class=\"cutelink3\">$starred_set $set_name ($num_sets)<br></a>";
}

//echo "<li>$key3 | $key2 | $key1</li>"; 
$i++;
}
}


echo "</div>

		</form>	
		$ispraak_footer
	</div>
	</body>
	
	</html>

";

//close your connection to the DB
mysqli_close($msi_connect);
}


?>