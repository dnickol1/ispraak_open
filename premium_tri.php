<?php

/*

This page only loads in the event a TTS activity does not have a cached TTS file on the server.
When no TTS file is found, users can click on a speaker icon to dynamically generate and listen
to a new file, based on the language and text from the activity. 

Not all languages have a TTS voice available at this time. 

*/

//Starts or continues session from previous PHP page

session_start();

//Get all from query string 

$mykey=$_GET['mykey'];
$mykey2=$_GET['mykey2'];
$mylang=$_GET['lang'];
$btext=$_GET['btext'];
$option = "all";

//Currently no TTS support for Amharic, Croatian, Hebrew, Farsi, Swahili, Urdu or Zulu 

if ($mylang == "am" || $mylang == "hr" || $mylang == "he" || $mylang == "fa" || $mylang == "sw" || $mylang == "ur" || $mylang == "zu")
{
$option = "No TTS available";
echo "<img src=\"images/inactive_speaker.png\" width=\"40\"  align=\"right\">";
}
else
{
$option = "TTS available"; 
echo "<a href=\"google_tts.php?lang=$mylang&mykey=$mykey&mykey2=$mykey2&btext=$btext&vg=random\"><img src=\"images/speaker.png\" width=\"40\"  align=\"right\"></a>"; 
}

?>

