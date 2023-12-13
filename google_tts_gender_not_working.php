<?php

// Updated on June 7, 2022
// Billing enabled, JSON array updated with credentials from Google Console
// includes the autoloader for libraries installed with composer

require_once '../../vendor/autoload.php';


//Get database variables: this path is  confirmed 
include_once("../../config_ispraak.php");

//examples query pasted below to call this page
//https://www.ispraak.net/google_tts.php?lang=fr&mykey=1651611033&mykey2=Z28a5bcdbb44feb3978d701e3d7bcfac&btext=bonjour&vg=FEMALE
//https://www.ispraak.net/google_tts.php?lang=fr&mykey=1651199295&mykey2=xfb394b35bfada64b5453261b450ec41&btext=prego&vg=FEMALE

//This will make a file with the name mykey_mykey2_GENDER.mp3
//and store it in ispraak/audio_saves

// Imports the Cloud Client Library
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

//Use service account credentials for this API

	$projectID = "ispraak-neh";
	$serviceAccountPath = "ispraak-neh-google.json"; 
	
	putenv('GOOGLE_APPLICATION_CREDENTIALS=../../ispraak-neh-google.json');
    //$client->useApplicationDefaultCredentials();
	
    $config = [
        'keyFilePath' => $serviceAccountPath,
        'projectId' => $projectId,
    ];


//Get all 3 variables from query string just like the old way from premium_tri.php on ispraak
//You can see that mykey2 also needs to be updated

$mykey=$_GET['mykey'];
$mykey2=$_GET['mykey2'];
$mylang=$_GET['lang'];
$btext=$_GET['btext'];
$voice_gender=$_GET['vg']; //Current options only MALE and FEMALE
$voice_speed=$_GET['vs']; // Current options fast or slow

//Remove the double asterix wildcard text so it is not spoken in TTS
$double_asterix = array("**","••");
$btext = str_replace($double_asterix, "", $btext);

//randomly decide between a male or female voice instead of taking user suggestion
//this will be overwritten if a male voice does not exist below 

$voice_gender = rand(0, 1) ? 'MALE' : 'FEMALE';

//create cookie for gender selection
setcookie("rand_gender", $voice_gender, time()+7200, '/'); 

//Convert to Proper Language Code xx-XX for Google API

$language = "error";

//no Amharic Support (am)
if ($mylang == "ar") { $language = "ar-XA"; }
if ($mylang == "bn") { $language = "bn-IN"; }
//no Male Voice for Catalan
if ($mylang == "ca") { $language = "ca-ES"; $voice_gender = "FEMALE";}
if ($mylang == "zh") { $language = "cmn-CN"; }
//no Croatian support (hr)
//no Male Voice for Czech 
if ($mylang == "cs") { $language = "cs-CZ"; $voice_gender = "FEMALE";}
if ($mylang == "da") { $language = "da-DK"; }
if ($mylang == "nl") { $language = "nl-NL"; }
if ($mylang == "en") { $language = "en-US"; }
//no Male Voice for Finnish 
if ($mylang == "fi") { $language = "fi-FI"; $voice_gender = "FEMALE";}
if ($mylang == "fr") { $language = "fr-FR"; }
if ($mylang == "de") { $language = "de-DE"; }
//no Male Voice for Greek 
if ($mylang == "el") { $language = "el-GR"; $voice_gender = "FEMALE";}
//no Hebrew Support (he)
if ($mylang == "hi") { $language = "hi-IN"; }
//no Male Voice for Hungarian
if ($mylang == "hu") { $language = "el-GR"; $voice_gender = "FEMALE";}
if ($mylang == "id") { $language = "id-ID"; }
if ($mylang == "it") { $language = "it-IT"; }
if ($mylang == "ja") { $language = "ja-JP"; }
if ($mylang == "ko") { $language = "ko-KR"; }
if ($mylang == "no") { $language = "nb-NO"; }
//no Persian or Farsi Support (fa)
if ($mylang == "pl") { $language = "pl-PL"; }
if ($mylang == "pt") { $language = "pt-BR"; }
//no Male Voice for Romanian
if ($mylang == "ro") { $language = "ro-RO"; $voice_gender = "FEMALE";}
if ($mylang == "ru") { $language = "ru-RU"; }
if ($mylang == "es") { $language = "es-US"; }
//no Swahili Support (sw)
if ($mylang == "sv") { $language = "sv-SE"; }
//no Male Voice for Thai
if ($mylang == "th") { $language = "th-TH"; $voice_gender = "FEMALE";}
if ($mylang == "tr") { $language = "tr-TR"; }
//no Male Voice for Ukrainian
if ($mylang == "uk") { $language = "uk-UA"; $voice_gender = "FEMALE";}
//no Urdu Support (sw)
if ($mylang == "vi") { $language = "vi-VN"; }
//no Zulu Support (zu)


//check for an instructor regional TTS override on the following languages

$instructor_email = $_COOKIE["cookie_instructor_email"];  
	
if ($mylang == "nl" || $mylang == "zh" || $mylang == "nl" ||  $mylang == "en" ||  $mylang == "fr" || $mylang == "pt" || $mylang == "es")
{

	//Connect to the database
	$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

	//do language preferences exist for this user? 
	$myresult = mysqli_query($msi_connect, "SELECT * FROM ispraak_user_prefs where email='$instructor_email' AND language='$mylang' ORDER BY id DESC");
	$rowcount=mysqli_num_rows($myresult);	
	if ($rowcount > 0)
	{
			$i==0; 
			$tts_pref=mysqli_result($myresult,$i,"tts_pref");
			$language = $tts_pref; 	
			
			$note = "User lang pref set for $language for $instructor_email"; 
			setcookie("tts_override", $note, time()+7200, '/'); 

	}
	mysqli_close($msi_connect);
}

//old method to include gender in file name
//$fullpath = "audio_saves/".$mykey."_".$mykey2."_".$voice_gender.".mp3";

$fullpath = "audio/".$mykey."_".$mykey2.".mp3";

//echo file_put_contents($fullpath, $result) . '';
 
// instantiates a client
$client = new TextToSpeechClient($config);

// sets text to be synthesised
$synthesisInputText = (new SynthesisInput())
    ->setText($btext);

// build the voice request, select the language code ("en-US") and the ssml
// voice gender

//Assume ssmlGender is FEMALE unless indicated MALE

//speakingRate

//April 2023, try to build the voice name
//$voice_name = $language . '-Standard-A' 
//Standard A - seems to be female




if ($voice_gender == "MALE")
{

$voice = (new VoiceSelectionParams())
    ->setLanguageCode($language)
    ->setSsmlGender(SsmlVoiceGender::MALE);
    
    //create cookie for gender selection
	setcookie("rand_gender_male_only", $voice_gender, time()+7200, '/'); 


    
}
else
{
$voice = (new VoiceSelectionParams())
    ->setLanguageCode($language)
    ->setSsmlGender(SsmlVoiceGender::FEMALE);
    
    //create cookie for gender selection
    setcookie("rand_gender_female_only", $voice_gender, time()+7200, '/'); 
    
    
}

//voice was this :     ->setLanguageCode('en-US')
//voice was this: --- ::FEMALE

// Effects profile
$effectsProfileId = "telephony-class-application";

// select the type of audio file you want returned
$audioConfig = (new AudioConfig())
    ->setAudioEncoding(AudioEncoding::MP3)
    ->setEffectsProfileId(array($effectsProfileId));

// perform text-to-speech request on the text input with selected voice
// parameters and audio file type
$response = $client->synthesizeSpeech($synthesisInputText, $voice, $audioConfig);
$audioContent = $response->getAudioContent();

// the response's audioContent is binary
//file_put_contents('output.mp3', $audioContent);
file_put_contents($fullpath, $audioContent);

echo "<script>

function replay() {
    var audio = document.getElementById('audio1');
    if (audio.paused) {
        audio.play();
    }else{
        audio.currentTime = 0
    }
    
    parent.document.getElementById('tts_not_available').style.display=\"inline\";
    
    
}

</script>";


echo "<img src=\"images/replay.png\" onclick=\"replay()\" width=\"35\" align=\"right\"></a>"; 
echo "<audio controls autoplay hidden id=\"audio1\"><source src=\"$fullpath\" type=\"audio/mpeg\">Your browser does not support the audio playback element.</audio>";
  
  
/*

Hiding the audio element will stop safari from autoplaying, but this will only happen on the first call

echo "
<audio controls>
  <source src=\"$fullpath\" type=\"audio/mpeg\">
Your browser does not support the audio element.
</audio>";
*/


//echo 'Audio content written to "output.mp3"' . PHP_EOL;

?>