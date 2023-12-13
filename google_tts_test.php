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


function list_voices(): void
{
    // create client object
    $client = new TextToSpeechClient();

    // perform list voices request
    $response = $client->listVoices();
    $voices = $response->getVoices();

    foreach ($voices as $voice) {
        // display the voice's name. example: tpc-vocoded
        printf('<br>Name: %s' . PHP_EOL, $voice->getName());

        // display the supported language codes for this voice. example: 'en-US'
        foreach ($voice->getLanguageCodes() as $languageCode) {
            printf('Supported language: %s' . PHP_EOL, $languageCode);
        }

        // SSML voice gender values from TextToSpeech\V1\SsmlVoiceGender
        $ssmlVoiceGender = ['SSML_VOICE_GENDER_UNSPECIFIED', 'MALE', 'FEMALE',
        'NEUTRAL'];

        // display the SSML voice gender
        $gender = $voice->getSsmlGender();
        printf('SSML voice gender: %s' . PHP_EOL, $ssmlVoiceGender[$gender]);

        // display the natural hertz rate for this voice
        printf('Natural Sample Rate Hertz: %d' . PHP_EOL,
            $voice->getNaturalSampleRateHertz());
    }

    $client->close();
}


$output = list_voices(); 

echo "$output"; 


?>