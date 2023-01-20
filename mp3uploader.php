<?

/*

Looks at file type and size and determines if user-submitted file is
appropriate to move from temp into upload folder. A random file name is generated. 

Linked to from makeit.php and redirects to edit.php

If a problem appears with filename, the mp3link variable is set to 1 to ensure TTS modeling. 

*/

//Continues Session from previous PHP page
session_start();

//Comment the below off to turn off error warnings
error_reporting(0);

//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");


if ((($_FILES["file"]["type"] == "audio/mp3")
|| ($_FILES["file"]["type"] == "audio/mpeg")
|| ($_FILES["file"]["type"] == "audio/mp4"))
&& ($_FILES["file"]["size"] < 1999999))
{
  if ($_FILES["file"]["error"] > 0)
    {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
    }
  else
    {
		$myfilename = $_FILES["file"]["name"];
		$myfile_basename = substr($myfilename, 0, strripos($myfilename, '.'));
		$myfile_ext = substr($myfilename, strripos($myfilename, '.')); // strip name
		$newfilename = md5(time() . $file_basename) . $myfile_ext;
		$_SESSION['mp3link']= $newfilename;

    	if (file_exists("uploadmp3/" . $newfilename))
      	{
	  		echo "<hr><h3>";
      		echo $_FILES["file"]["name"] . " already exists. Please give your file a unique name and re-upload. </h3> Press Back to Continue<hr>";
      	}
    	else
      	{

	  		move_uploaded_file($_FILES["file"]["tmp_name"], "uploadmp3/" . $newfilename);
	  		$_SESSION['mp3link']= $newfilename;
	  		$mylink = $_SESSION['mp3link'];
	  		header( 'Location: edit.php' ) ;
        }
    }
}
else
{	  
	$_SESSION['mp3link']= "1"; 
 
	echo "$ispraak_header <form id=\"form_1007732\" class=\"ispraak_form\"  method=\"post\" action=\"makeit.php\">
<div class=\"form_description\">$ispraak_logo Zut alors! Sorry, an invalid file was detected.<br>Files must be in the MP3 format and no larger than 2mb. 
 <br><br></div>

<br><br><center>

	<a href=\"edit.php\">Continue with text-to-speech in lieu of file upload</a>
		
			</center>
			<br>
	</p>
			<ul >
			</ul>
		</form>	
$ispraak_footer
	</div>
	<img id=\"bottom\" src=\"bottom.png\" alt=\"\">
	</body>
</html>";

  }
?>