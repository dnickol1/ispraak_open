<?php

session_start();

include_once("../../config_ispraak.php");

//connection to database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

//query
$language=$_POST['language'];


$result = mysqli_query($msi_connect,"SELECT * FROM ispraak_sets where language='$language' AND shared='public' GROUP BY set_name");

$rowcount=mysqli_num_rows($result);	

if ($rowcount <1)
{
	echo "<center>No activity sets found"; 
}
else{

echo "$ispraak_header
	
		<form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"publicset.php\">
			
		$ispraak_logo<br><br><br>
					
		<center>Below are given some example of ($language) public activity sets!</center><br><br>";

//show all existing sets inside this form

if ($rowcount <1)
{
	echo "<center>Sorry! No public activity sets found for this language!</center>"; 
}	
else{

$i=0;
while ($i < $rowcount) 
{

$rowcount=mysqli_num_rows($result);	
$set_name=mysqli_result($result,$i,"set_name");
$set_id=mysqli_result($result,$i,"set_id");

//Should look like this: https://www.ispraak.net/sets_students.php?id=75
echo "<li><a href=\"sets_students.php?id=$set_id\" class=\"cutelink3\">$set_name</a> ($language) $shared </li>";

$i++;
}
}

//close your connection to the DB
mysqli_close($msi_connect);
}
echo"			
			
		</form>	
		$ispraak_footer
	</div>
	</body>
	
</html>";

?>