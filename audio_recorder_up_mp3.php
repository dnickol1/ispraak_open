<?php

/*

This page is called from audio_recorder_mp3.php and takes the temp file from the recorder
and renames and relocates it to the uploadmp3 folder. File size and type are checked. 

Since there is no output, the prior page continues through its functions and ultimately 
redirects to edit.php 

*/

//get config variables 
include_once("../../config_ispraak.php");

session_start();

$filename = $_SESSION['mp3link']; 

//temporary name that PHP gave to the uploaded file
$input = $_FILES['audio_data']['tmp_name']; 

//the name has been changed to a unique one based on mykey and mykey2
$output = $_FILES['audio_data']['name'].".mp3"; 

//the full server path (specified in the config file) to move this recording
$output2 = $ispraak_full_mp3_server_path . $output; 

//File records to about 6kb/sec, so a 60 second file would be 360,000 bytyes
$fsize = $_FILES['audio_data']['size']; 

//File records to audio type - should not accept others
$ftype = $_FILES['audio_data']['type']; 

//If the file is too large or of the wrong type, we want to redirect to an error screen. 
if ($fsize > 800000 || $ftype != "audio/mpeg")
{

 $note = "Problem with this file type which is $fsize and $ftype"; 
 setcookie("cookie_file_error", $note, time()+7200, '/'); 

}
else
{
//move the file from temp name to local folder using $output name
$moved = move_uploaded_file($input, $output2);

	if($moved) 
	{
  		$note = "Successfully uploaded"; 
  		 setcookie("cookie_file_error", $note, time()+7200, '/'); 
	} 
	else 
	{
		$note = $_FILES["audio_data"]["error"];
		setcookie("cookie_file_error", $note, time()+7200, '/'); 
	}

}

?>





