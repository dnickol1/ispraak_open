<?php

/*

This page is called by index.html and monitors the user's input to provide alerts if the selected
language and the detected language differ. An alert will also appear if a text string is too long. 

*/

include_once("../../config_ispraak.php");

//PHP Pear Packages Needed
ini_set("include_path", '/home2/dnickol1/php:' . ini_get("include_path") );

require_once "Text/LanguageDetect.php";


function detect_all_languages($text,$expected_language)
{
    
    //Remove the double asterix wildcard text so it is not spoken in TTS
	$double_asterix = array("**","••");
	$text = str_replace($double_asterix, "", $text);


	//Use Pear library to get top 3 most likely languages (for those supported)
    $ld = new Text_LanguageDetect();
    $ld->setNameMode(2);
    $language = $ld->detectSimple($text);
    
    //Now you have an array of the three most likely languages
    $results = $ld->detect($text, 3);

    $i=0;

	$first_lang = "zz";
	$second_lang = "zz";
	$third_lang = "zz";

    foreach ($results as $language => $confidence) {

        $finalstring=$language . ': ' . number_format($confidence, 2);
        //echo"<br><br>";
        //echo $finalstring . "\n";
        if ($i == 0) { $first_lang = $language;}
        if ($i == 1) { $second_lang = $language;}
        if ($i == 2) { $third_lang = $language;}
        
        $i++;
    }
    
    //Not all languages are supported by the LanguageDetect library so we will
    //check against pregmatch charset ranges and set $first_lang to $expected_language if true
    
    if ($expected_language == "zh")
			{
				if (preg_match("/\p{Han}+/u", $text)) 
				
					{
						$first_lang = $expected_language; 
					}
					else
					{
						$first_lang = "zz"; 
					}
	}

	if($expected_language == "ja")
		{
			if((preg_match('/\p{Han}|\p{Katakana}|\p{Hiragana}/u', $text)))
			{
				$first_lang = $expected_language;
			}
			else
			{
				$first_lang = "zz"; 
			}
	}

	if($expected_language == "ko")
	{
		if(preg_match('/[\x{3130}-\x{318F}\x{AC00}-\x{D7AF}]/u', $text))
		{
			$first_lang = $expected_language;
		}
		else
		{
			$first_lang = "zz";
		}
	
	}

	if($expected_language == "he")
	{
		if(preg_match('/[א-ת]/',$text))
		{
			$first_lang = $expected_language;
		}
		else
		{
			$first_lang = "zz";
		}
	}

	if($expected_language == "am")
	{
		if(preg_match('/\p{Ethiopic}/u', $text))
		{
			$first_lang = $expected_language;
		}
		else
		{
			$first_lang = "zz";
		}
	}

	if($expected_language == "el")
	{
		if(preg_match('/\p{Greek}/u', $text))
		{
			$first_lang = $expected_language;
		}
		else
		{
		    $first_lang = "zz";	
		}
	}

	if($expected_language == "th")
	{
		if(preg_match('/\p{Thai}/u', $text))
		{
			$first_lang = $expected_language;
		}
		else
		{
			$first_lang = "zz";
		}
	}

	if($expected_language == "ru")
	{
		if(preg_match('/[а-яА-ЯёЁ]+/u', $text))
		{
			$first_lang = $expected_language;
		}
		else
		{
			$first_lang = "zz";
		}
	}

    //Let's not confirm if our expected language is one of our top three most likely
    
    if($expected_language==$first_lang || $expected_language==$second_lang || $expected_language==$third_lang)
    {
        return true;
    }
    else
    {
        return false;
    }

}

// Get input from AJAX request which includes the expected language and the actual text entered by the user

  $input = $_GET['input'];
  $language=$_GET['language'];

// Use our outpoutLanguage function to get the correct two letter code converted into a string (fr --> French)  
  $readable_lang = outputLanguage($language); 

// We have no good way to identify Catalan or Zulu, so we will treat these as exceptions
  
if($language!="ca" && $language!="zu")
{
  
  	//too short of a string and language detection is unreliable, too long and an error will occur 
  
  	if(strlen($input)>39 && strlen($input)<350)
  	{
   
   		if (!detect_all_languages($input, $language) )
   		{
   
      		echo "<p style=\"color:red\">$readable_lang not detected. <br><br>Please confirm language selection.</span>"; 

   		} 
   		else
 		 {

   			echo "$readable_lang detected!  <br><br>Limit 370 characters.  ";
   			
 		 }
  }
}

// We have no good way to identify Catalan or Zulu, so we will treat these as exceptions
  
if($language!="ca" &&  $language!="zu")
  	{
  		if(strlen($input)<40 && strlen($input)<350)
  			{
  				echo "Enter a short contextualized sentence in $readable_lang. <br><br>Limit 370 characters.  "; 
  			}
	}
else
	{
		echo"Very short contextualized texts work best for speech recognition and speech synthesis. <br><br>(370 character limit)";
	}

//For all languages, warn if the string is too long 
  
if(strlen($input)>350)
    {
    
    	echo "<p style=\"color:red\">Text is too long!</p>"; 
    }
    
//Do a wildcard suggestion for certain phrases
if (strpos($input, '我叫') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //chinese
if (strpos($input, '我的名字是') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //chinese 2
if (strpos($input, 'Jmenuji se') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //czech 
if (strpos($input, 'Mijn naam is') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //dutch
if (strpos($input, 'My name is') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //english
if (strpos($input, 'my name is') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //english lowercase
if (strpos($input, 'Je m\'appelle ') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //french
if (strpos($input, 'je m\'appelle ') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //french lowercase
if (strpos($input, 'Mein Name ist') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //german
if (strpos($input, 'Ich heiße') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //german 2
if (strpos($input, 'mein Name ist') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //german lowercase
if (strpos($input, 'ich heiße') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //german 2 lowercase
if (strpos($input, 'με λένε') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //greek 1
if (strpos($input, 'λέγομαι') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //greek 2
if (strpos($input, 'Mi chiamo') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //italian
if (strpos($input, 'mi chiamo') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //italian lowercase
if (strpos($input, '私の名前は') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //japanese
if (strpos($input, '내 이름은') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //korean
if (strpos($input, 'mam na imię') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //polish
if (strpos($input, 'Meu nome') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //portuguese
if (strpos($input, 'meu nome') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //portuguese lowercase
if (strpos($input, 'Numele meu este') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //romanian
if (strpos($input, 'Menya зовут') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //russian
if (strpos($input, 'Me llamo') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //spanish
if (strpos($input, 'Mi nombre') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //spanish 2
if (strpos($input, 'me llamo') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //spanish lowercase
if (strpos($input, 'mi nombre') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //spanish 2 lowercase
if (strpos($input, 'Jag heter') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //swedish 
if (strpos($input, 'Benim adım') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //turkish
if (strpos($input, 'Tên tôi là') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //vietnamese
if (strpos($input, 'Мене звати') !== false) { echo "<p style=\"color:blue\">Use ** to create wildcards, e.g. My name is **name</p>"; }    //ukrainian

?>

