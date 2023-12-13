<?php

/**
 * Fetches a single field from a specific row in a MySQLi result set.
 * 
 * @param mysqli_result $res MySQLi result set.
 * @param int $row Row number to fetch the data from.
 * @param int $field Field index to retrieve.
 * @return mixed The requested field's value or NULL on failure.
 */
function mysqli_result($res, $row, $field = 0) {
    if (!is_object($res)) {
        //print 'object is expected in param1, ' . gettype($res) . ' is given';
        return NULL;
    }

    $res->data_seek($row);
    $datarow = $res->fetch_array();
    return $datarow[$field];
}

/**
 * Retrieves the IP address of the client.
 * 
 * @return string IP address of the client.
 */
function getIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * Compares two strings (instructor text and student effort in Pinyin) and highlights differences.
 * 
 * @param string $instructor_text_pinyin The text from the instructor.
 * @param string $student_effort_pinyin The text from the student.
 * @return void
 */
function find_mistakes($instructor_text_pinyin, $student_effort_pinyin) {
    //converting the instructor text to array
    $array = explode(" ", $instructor_text_pinyin);
    //print_r($array);
    //converting the student text to array
    $array_student = explode(" ", $student_effort_pinyin);
    //print_r($array_student);
    //initialising an empty string to store the output
    $final_string = " ";

    //for loop to iterate through the array and display the result
    for ($i = 0; $i < count($array_student); $i++) {
        if (!in_array($array_student[$i], $array)) {
            $word = $array_student[$i];
            //if the word is in the student array
            if (in_array($word, $array_student))  {
                $final_string .= ' <del>' . $word . '</del>';
            } else {
                $final_string .= ' ' . $word;
            }
        } else {
            $final_string .= ' ' . $array_student[$i];
        }
    }

    echo "<br><span style=\"color:gray\">$final_string</span>";
}

/**
 * Calculates a numerical score based on the comparison of two strings, considering wildcards.
 * 
 * @param string $instructor_text The text from the instructor.
 * @param string $student_effort The text from the student.
 * @return int The calculated score.
 */

//this function returns a numerical integer for wildcard sets
function findWildCardScore($instructor_text, $student_effort)
{
    // punctuation is a problem as always
    // this would DO all punctuation
    //$instructor_text = preg_replace("#[[:punct:]]#", "", $instructor_text); 
    //$student_effort = preg_replace("#[[:punct:]]#", "", $student_effort); 
    // new approach to keep only certain symbols $,',%,-,*
    //$instructor_text = preg_replace("/(?![—$-'–%-*−])\p{P}/u", "", $instructor_text);
    //$student_effort = preg_replace("/(?![—$'–%-*−-])\p{P}/u", "", $student_effort);

    $instructor_text = strtr($instructor_text, array('?' => '', '.' => '', ',' => ''));
    $student_effort = strtr($student_effort, array('?' => '', '.' => '', ',' => ''));

    $instructor_text = strtolower($instructor_text);
    $student_effort = strtolower($student_effort);

    // converting the instructor text to array
    $array = explode(" ", $instructor_text);

    // converting the student text to array
    $array_student = explode(" ", $student_effort);

    // calculating the length of the instructor length
    $length = count($array);

    // initialising an empty array
    $student = [];

    // looping through the instructor array to remove the strings starting with **
    for ($i = 0; $i < $length; $i++) {
        if (strpos($array[$i], '**') !== false) {
            unset($array[$i]);
        }
    }

    // reindexing array after unset
    $array = array_values($array);

    // checking if the instructor array elements are present in the student array after removing the strings with **
    for ($i = 0; $i < count($array); $i++) {
        if (!in_array($array[$i], $array_student) && $array[$i] != '') {
            array_push($student, $array[$i]);
        }
    }

    // finding the length of the instructor array
    $instructor_count = count($array);

    // finding out the length of the student array
    $student_count = count($student);

    // debugging
    // print_r($array);
    // print_r($student);

    // calculating the score (using floor function)
    $score = floor((($instructor_count - $student_count) / $instructor_count) * 100);

    return $score;
}

//This is a function to remove punctuations and lowercase.
function remove_punctuation_and_lowercase($any_text, $language)
{
	//Build an default language-agnostic array with unwanted punctuation
	$punctuation = array ("!","¡",":","。","，","。",",","\"","?", ".", "¿","”","“","≈");
	
	//the language variable may be lowercase, need to uppercase for switch
    $language = strtoupper($language);
    
	//Put in a language-specific array of punctuation
	switch($language) 
	{
	
		case 'AM':
		$punctuation = array ("?","!","¡","።","(",")","[","]","{","}","/","%","–","‹","›","«","»","፡","፣","፤","፥","፦","፧","-","…","."); 
		break;    

		case 'AR':
		$punctuation = array(":","؟",".","،","¡","¿","?","!","«","»");        
		break;

		case 'BN':
		$punctuation = array("!",".","?","(",")","[","]","{","}",":",";","`","‘","’","—","।","‘","’"); 
		break;

		case 'CA':
		$punctuation = array("/","!",".",",","¡","…","(",")",":",";","-","¿","?","«","»","‘","’","\""); 
		break;
		
		case 'ZH':
		$punctuation = array("，","。",",","、","：",";","？","！","「","『","』","」","《","》","…","·","\"");
		break; 
		
		case 'HR':
		$punctuation = array(".",",","!","/","{","}","(",")","[","]",":",";","`","-","…","?","‘","\"","„"); 
		break;

		case 'CS':
		$punctuation = array(",",".","-",":","…","(",")","{","}","?","„","!",";","‘","[","]","\"","/","«","»");
		break;

		case 'DA':
		$punctuation = array(",",".","—","«","»","?","!",";",":","’","…","-","/","(",")","[","]","{","}");
		break;

		case 'NL':
		$punctuation = array(".",",","!","_",";",":","-","?","…","(",")","`","‘","’","”","“","—",";");
		break;

		case 'EN':
		$punctuation = array(".",",","?","!","<",">","(",")","{","}","[","]","…","’","/","`",":",";","-","“","”","\"","¡");
		break;

		case 'FI':
		$punctuation = array(".",",","!","?","/","\"","|","–","-","”","~","…","(",")","{","}","[","]","«","»",":",";");
		break;

		case 'FR':
		$punctuation = array ("!","¡",":",",",".","\"","?",".","¿","”","“","«","»","…",";","(",")","{","}","[","]");
		break;

		case 'DE':
		$punctuation = array("«","»","…","(",")","{","}","[","]","?",":","'",";","-","!",",",".","/","-","—","\"","„");
		break;

		case 'EL':
		$punctuation = array(".",",","·",";",":","!","…","(",")","«","»","-","’");
		break;

		case 'HE':
		$punctuation = array ("?","!","¡",".",":","-","|","…","”","„","׃‎",":","׀","־‎","׳","'","﬩","־");
	
		break;

		case 'HI':
		$punctuation = array("|",",",":",";","-","(",")","[","]","{","}","…","०","_",":","-","!","?","S","^","=",",");
		break;

		case 'HU':
		$punctuation = array(".",",","!","_",";",":","-","?","…","(",")","`","„","-","–");
		break;

		case 'ID':
		$punctuation = array(".",",","-","–","’","?","!","#","&","2","(",")","\"");
		break;

		case 'IT':
		$punctuation = array(",",".",";",":","!","?","-","(",")","[","]","'","/","«","»","“","”");
		break;
		
		case 'JA':
		$punctuation = array("？","「","」","【","】","『","』","、","…","。","~","・","〽","(",")","?","？","?","!","J","｛","｝","゛","゜","っ","ー","ゝ","ヽ","ゞ","ヾ");
		break;

		case 'KO':
		$punctuation = array(".",",","-","?","/","~","…","'","!",":","”","“","(",")","ㆍ");
		break;

		case 'NO':
		$punctuation = array(".",",","!","_",";",":","-","?","…","(",")","`","”","“","/");
		break;

		case 'FA':
		$punctuation = array(":","؟",".","،","؛","«","»","/","[","]","{","}",",","…","—");
		break;
 
		case 'PL':
		$punctuation = array(".",",","?","!","/","<",">","(",")","{","}","[","]","…","’","/","`",":",";","„","«","»","-");
		break; 

		case 'PT':
		$punctuation = array(".",",","?","!","<",">","(",")","{","}","[","]","…","’","/","`",":",";","„","«","»","-","~","“","”","\"");
		break;

		case 'RO':
		$punctuation = array(".",",","!","_",";",":","-","?","…","(",")","`","{","}","[","]","—","„","”","«","»","’");
		break;

		case 'RU':
		$punctuation = array(".",",","…",":",";","?","!","-","—","(",")","«","»","’","“","”");
		break;

		case 'ES':
		$punctuation = array(".",",","?","!","<",">","(",")","{","}","[","]","…","’","/",":",";","“","”","«","»","-","\"","¿","¡");
		break;

		case 'SW':
		$punctuation = array(".",",","?","!","<",">","(",")","{","}","[","]","…","’","/","`",":",";","-","“","”");
		break;

		case 'SV':
		$punctuation = array(".",",","?","!",":",";","-","<",">","«","»","…","(",")","[","]","{","}","/","I","—","’","“","”");
		break;

		case 'TH':
		$punctuation = array(".",",","…","(",")","ๆ","ฯ","-","?",";",":","ฯลฯ","๏","๛","⠆","“","”","!","«","»","/");
		break;

		case 'TR':
		$punctuation = array(".",",",":","?","!",";","“","”","’","…","—","-","/","\"","[","]","{","}","(",")");
		break;

		case 'UK':
		$punctuation = array(".",",","/","?","!","«","»",":",";","„","“","\"","…","—","-","(",")","[","]","{","}");
		break;

		case 'UR':
		$punctuation = array("¯","-:",":","…","؟","!","(",")","-","؛","{","}","[","]",".",",");
		break;

		case 'VI':
		$punctuation = array("!","?",".",",","…",";",":","—","’","/","(",")","“","”","[","]","-");
		break;

		case 'ZU':
		$punctuation = array ("!",":",";","-",".",",","?","。",",","”","“","≈","(",")","[","]","{","}","\"","’");
		break;

	}
	

    //Swap out line breaks with a single space
    $any_text = str_replace(array("\r", "\n"), ' ', $any_text);

	//Swap out double spaces with a single space
    $any_text = str_replace(array("  "),' ', $any_text);
    
	//Substitute any punctuation with a blank space
	$any_text = str_replace($punctuation,"",$any_text); 
     
    //Lowercase entire string
    $any_text = strtolower($any_text);
    

    //Trim white spaces on front and back of string
    $any_text = trim($any_text);
    
    //Return the string without punctuation or uppercase characters 
    return $any_text; 
}

//function to get Language from Language Code
function outputLanguage($language){

    switch($language){
    
        case 'am':
            return "Amharic";
            break;
        
        case 'ar':
            return "Arabic";
            break;
    
        case 'bn':
            return "Bengali";
            break;
    
        case 'ca':
            return "Catalan";
            break;
    
        case 'zh':
            return "Chinese";
            break;
    
        case 'hr':
            return "Croatian";
            break;
    
        case 'cs':
            return "Czech";
            break;
    
        case 'da':
            return "Danish";
            break;
    
        case 'nl':
            return "Dutch";
            break;
    
        case 'en':
            return "English";
            break;
        
        case 'fi':
            return "Finnish";
            break;
        
        case 'fr':
            return "French";
            break;
    
        case 'de':
            return "German";
            break;
    
        case 'el':
            return "Greek";
            break;
    
        case 'he':
            return "Hebrew";
            break;
        
        case 'hi':
            return "Hindi";
            break;
    
        case 'hu':
            return "Hungarian";
            break;
    
        case 'id':
            return "Indonesian";
            break;
        
        case 'it':
            return "Italian";
            break;
    
        case 'ja':
            return "Japanese";
            break;
    
        case 'ko':
            return "Korean";
            break;
        
        case 'no':
            return "Norwegian";
            break;
    
        case 'fa':
            return "Persian";
            break;
        
        case 'pl':
            return "Polish";
            break;
    
        case 'pt':
            return "Portugese";
            break;
        
        case 'ro':
            return "Romanian";
            break;
    
        case 'ru':
            return "Russian";
            break;
    
        case 'es':
            return "Spanish";
            break;
    
        case 'sw':
            return "Swahili";
            break;
    
        case 'sv':
            return "Swedish";
            break;
    
        case 'th':
            return "Thai";
            break;
    
        case 'tr':
            return "Turkish";
            break;
    
        case 'uk':
            return "Ukrainian";
            break;
    
        case 'ur':
            return "Urdu";
            break;
    
        case 'vi':
            return "Vietnamese";
            break;
    
        case 'zu':
            return "Zulu";
            break;
    
    }
}

define("encryption_method", "AES-128-CBC");
define("key", "53fdc44464676371607b4fd06d22a6f5");

function encrypt($data) {
    $key = key;
    $plaintext = $data;
    $ivlen = openssl_cipher_iv_length($cipher = encryption_method);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
    $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
    return $ciphertext;
}
function decrypt($data) {
    $key = key;
    $c = base64_decode($data);
    $ivlen = openssl_cipher_iv_length($cipher = encryption_method);
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len = 32);
    $ciphertext_raw = substr($c, $ivlen + $sha2len);
    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
    if (hash_equals($hmac, $calcmac))
    {
        return $original_plaintext;
    }
}


function hide_email_alt($em) 
{

	$stars = 3; // Min Stars to use
	$at = strpos($em,'@');
	if($at - 5 > $stars) $stars = $at - 5;
	$sl = strlen($em);
	if($sl > 20) $stars = 7; // Max stars to use
	$email_hidden = substr($em,0,5) . str_repeat('*',$stars) . substr($em,$at - 0);
	return $email_hidden; 
}

function hide_email($em) 
{

	$stars = 3; // Min Stars to use
	$at = strpos($em,'@'); //location of @ symbol
	$el = strlen($em); //length of full string
	$first_half = substr($em,0,$at); //string before @s symbol
	$second_half = substr($em, $at, $el);  //string after and including @ symbol
	$first_half_length = strlen($first_half); //will take a fraction of this to hide
	$fraction_of_fhl = round($first_half_length/2); //round to nearest whole number
	$email_hidden = substr($first_half, 0, $fraction_of_fhl) . '***' . $second_half; //build email string
	return $email_hidden; 
}

// This function is used as a goodbye message
function ReturnMessage($language,$score)
{
    switch($language)
    {
    case 'am':
        if($score>93)
        {
            $message='ምርጥ ስራ';
            return $message;
        }
        else
        {
            $message_error='መሞከርህን አታቋርጥ! መልካም አድል!';
            return $message_error;
        }
        break;

    case 'ar':
        if($score>93)
        {
            $message='أعمل عظيم';
            return $message;
        }
        else
        {
            $message_error='استمر في المحاولة! أتمنى لك كل خير!';
            return $message_error;
        }
        break;

    case 'bn':
        if($score>93)
        {
            $message='মহান কাজ!';
            return $message;
        }
        else
        {
            $message_error='চেষ্টা করে যাও! শুভকামনা!!';
            return $message_error;
        }
        break;

    case 'ca':
        if($score>93)
        {
            $message='Gran Obra!';
            return $message;
        }
        else
        {
            $message_error='Continua intentant-ho! Tot el millor!';
            return $message_error;
        }
        break;

    case 'zh':
        if($score >93)
        {
            $message='做得好';
            return $message;
        }
        else
        {
            $message_error='继续尝试!一切顺利';
            return $message_error;
        }
        break;

    case 'hr':
        if($score>93)
        {
            $message='Dobar posao';
            return $message;
        }
        else
        {
            $message_error='Nastavi pokušavati! Sve najbolje!';
            return $message_error;
        }
        break;

    case 'cs':
        if($score > 93)
        {
            $message='Skvělá práce';
            return $message;
        }
        else
        {
            $message_error='Zkoušej to dál! Vše nejlepší!';
            return $message_error;
        }
        break;

    case 'da':
        if($score > 93)
        {
            $message='Flot arbejde!';
            return $message;
        }
        else
        {
            $message_error='Bliv ved med at prøve! Alt det bedste!';
            return $message_error;
        }
        break;

    case 'nl':
        if($score >93)
        {
            $message='Goed gedaan!';
            return $message;
        }
        else
        {
            $message_error='Blijf proberen! Al het beste!';
            return $message_error;
        }
        break;

    case 'en':
        if($score >93)
        {
            $message='Great Work!';
            return $message;
        }
        else
        {
            $message_error='Keep practicing!';
            return $message_error;
        }
        break;

    case 'fi':
        if($score >93)
        {
            $message='Hienoa työtä!';
            return $message;
        }
        else
        {
            $message_error='Jatka yrittämistä! Kaikki parhaat!';
            return $message_error;
        }
        break;

    case 'fr':
        if($score >93)
        {
            $message='Bravo! Chapeau!';
            return $message;
        }
        else
        {
            $message_error='Continue de pratiquer!';
            return $message_error;
        }
        break;

    case 'de':
        if($score >93)
        {
            $message='Gut gemacht!';
            return $message;
        }
        else
        {
            $message_error='Weiter versuchen! Alles Gute!';
            return $message_error;
        }
        break;

    case 'el':
        if($score >93)
        {
            $message='Καταπληκτική δουλειά!';
            return $message;
        }
        else
        {
            $message_error='Συνέχισε να προσπαθείς! Τα καλύτερα!';
            return $message_error;
        }
        break;

    case 'he':
        if($score >93)
        {
            $message='עבודה נהדרת!';
            return $message;
        }
        else
        {
            $message_error='תמשיך לנסות! כל טוב!';
            return $message_error;
        }
        break;
        
    case 'hi':
        if($score >93)
        {
            $message='महान काम!';
            return $message;
        }
        else
        {
            $message_error='कोशिश करते रहो! शुभकामनाएं!';
            return $message_error;
        }
        break;

    case 'hu':
        if($score >93)
        {
            $message='nagyszerű munka!';
            return $message;
        }
        else
        {
            $message_error='próbálkozz! minden jót!';
            return $message_error;
        }
        break;

    case 'id':
        if($score >93)
        {
            $message='kerja Bagus!';
            return $message;
        }
        else
        {
            $message_error='Terus mencoba! Semua yang terbaik!';
            return $message_error;
        }
        break;

    case 'it':
        if($score >93)
        {
            $message='Ottimo Lavoro!';
            return $message;
        }
        else
        {
            $message_error='Continua a provare! Ti auguro il meglio!';
            return $message_error;
        }
        break;

    case 'ja':
        if($score >93)
        {
            $message='すごい仕事！';
            return $message;
        }
        else
        {
            $message_error='挑戦し続ける！ではごきげんよう！';
            return $message_error;
        }
        break;

    case 'ko':
        if($score >93)
        {
            $message='잘 했어!';
            return $message;
        }
        else
        {
            $message_error='계속 노력해! 모두 최고입니다！';
            return $message_error;
        }
        break;

    case 'no':
        if($score >93)
        {
            $message='Flott arbeid!';
            return $message;
        }
        else
        {
            $message_error='Fortsett å prøve! Beste ønsker!';
            return $message_error;
        }
        break;

    case 'fa':
        if($score >93)
        {
            $message='کارت عالی بود!';
            return $message;
        }
        else
        {
            $message_error='به تلاش ادامه بده بهترین ها!';
            return $message_error;
        }
        break;

    case 'pl':
        if($score >93)
        {
            $message='Świetna robota!';
            return $message;
        }
        else
        {
            $message_error='Próbuj dalej! Wszystkiego najlepszego!';
            return $message_error;
        }
        break;

    case 'pt':
        if($score >93)
        {
            $message='Ótimo trabalho!';
            return $message;
        }
        else
        {
            $message_error='Continue tentando! Tudo de bom!';
            return $message_error;
        }
        break;

    case 'ro':
        if($score >93)
        {
            $message='Buna treaba!';
            return $message;
        }
        else
        {
            $message_error='Continua sa incerci! Toate cele bune!';
            return $message_error;
        }
        break;

    case 'ru':
        if($score >93)
        {
            $message='Отличная работа!';
            return $message;
        }
        else
        {
            $message_error='Продолжайте пытаться! Всего наилучшего!';
            return $message_error;
        }
        break;

    case 'es':
        if($score >93)
        {
            $message='¡excelente trabajo!';
            return $message;
        }
        else
        {
            $message_error='¡Sigue intentándolo!';
            return $message_error;
        }
        break;

    case 'sw':
        if($score >93)
        {
            $message='Kazi nzuri!';
            return $message;
        }
        else
        {
            $message_error='Zidi kujaribu! Kila la kheri!';
            return $message_error;
        }
        break;

    case 'sv':
        if($score >93)
        {
            $message='Bra jobbat!';
            return $message;
        }
        else
        {
            $message_error='Fortsätt försöka! Med vänliga hälsningar!';
            return $message_error;
        }
        break;

    case 'th':
        if($score >93)
        {
            $message='การทำงานที่ดี!';
            return $message;
        }
        else
        {
            $message_error='พยายามต่อไป! ดีที่สุด!';
            return $message_error;
        }
        break;

    case 'tr':
        if($score >93)
        {
            $message='Harika iş!';
            return $message;
        }
        else
        {
            $message_error='Denemeye devam et! Herşey gönlünce olsun!';
            return $message_error;
        }
        break;

    case 'uk':
        if($score >93)
        {
            $message='Чудова робота!';
            return $message;
        }
        else
        {
            $message_error='Продовжуй пробувати! Все найкраще!';
            return $message_error;
        }
        break;

    case 'ur':
        if($score >93)
        {
            $message='عظیم کام!';
            return $message;
        }
        else
        {
            $message_error='کوشش جاری رکھیں! اللہ بہلا کرے!';
            return $message_error;
        }
        break;

    case 'vi':
        if($score >93)
        {
            $message='Công việc tuyệt vời!';
            return $message;
        }
        else
        {
            $message_error='Tiếp tục cố gắng! Tất cả những gì tốt nhất!';
            return $message_error;
        }
        break;

    case 'zu':
        if($score >93)
        {
            $message='Umsebenzi Omuhle!';
            return $message;
        }
        else
        {
            $message_error='Qhubeka uzame! Ngikufisela okuhle!';
            return $message_error;
        }
        break;
        
    }
    
    $message = "Nice work!";
    return $message; 

}

// This function checks if anticipated language matches submitted language
function detectLanguage($text, $expected_language)
{
    $ld = new Text_LanguageDetect();
    $ld->setNameMode(2);
    $language = $ld->detectSimple($text);

    $results = $ld->detect($text, 3);

    $i = 0;
    $first_lang = "zz";
    $second_lang = "zz";
    $third_lang = "zz";

    foreach ($results as $language => $confidence) {
        $finalstring = $language . ': ' . number_format($confidence, 2);
        // echo"<br><br>";
        // echo $finalstring . "\n";
        if ($i == 0) { 
            $first_lang = $language;
        }
        if ($i == 1) { 
            $second_lang = $language;
        }
        if ($i == 2) { 
            $third_lang = $language;
        }
        
        $i++;
    }

    // echo "<br> order: $first_lang , $second_lang , $third_lang <br>"; 
    if ($expected_language == $first_lang || $expected_language == $second_lang || $expected_language == $third_lang) {
        return true;
    } else {
        return false;
    }
}

// This function replaces apostrophes in a string
function apo_swap($string_with_apo)
{
    // Check set creation code for similar functionality for other symbols 
    $string_with_apo = str_replace("’", "'", $string_with_apo); 
    return $string_with_apo; 
}

// This function provides a transliteration for languages in which built-in PHP functions
// are not satisfactory 

function api_Transliterate($mylang, $bt, $azure_api_tranliteration_key) {
    $host = "https://api.cognitive.microsofttranslator.com";
    $path = "/translate?api-version=3.0";
    $region = "westus2"; 
    $subscription_key = $azure_api_tranliteration_key; 
    $endpoint = "https://api.cognitive.microsofttranslator.com/";
    $path = "/transliterate?api-version=3.0";

    // Define parameters for different languages
    $params = "";
    if ($mylang == "ja") {
        $params = "&language=$mylang&fromScript=jpan&toScript=latn";
    }
    if ($mylang == "ar") {
        $params = "&language=$mylang&fromScript=arab&toScript=latn";
    }
    if ($mylang == "he") {
        $params = "&language=$mylang&fromScript=hebr&toScript=latn";
    }

    // Utility function to generate a GUID
    if (!function_exists('com_create_guid')) {
        function com_create_guid() {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
    }

    // Function to perform the transliteration
    function Transliterate($host, $path, $key, $params, $content) {
        $region = "westus2"; 

        $headers = "Content-type: application/json\r\n" .
            "Content-length: " . strlen($content) . "\r\n" .
            "Ocp-Apim-Subscription-Key: $key\r\n" . 
            "Ocp-Apim-Subscription-Region: $region\r\n" .
            "X-ClientTraceId: " . com_create_guid() . "\r\n";

        $options = array(
            'http' => array(
                'header' => $headers,
                'method' => 'POST',
                'content' => $content
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($host . $path . $params, false, $context);
        return $result;
    }

    // Preparing request body for the transliteration API
    $requestBody = array(array('Text' => $bt));
    $content = json_encode($requestBody);

    // Call the transliteration function
    $result = Transliterate($endpoint, $path, $subscription_key, $params, $content);
    $json = json_encode(json_decode($result), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $obj = json_decode($result);
    $just_text = $obj[0];
    $new_text = $just_text->text; 

    return $new_text;
}

// Function to see if a French homophone was said in place of expected word
function confirm_mistake($word, $phrase) {
    // Step 0: Lowercase both the word and the phrase
    $word = mb_strtolower($word);
    $phrase = mb_strtolower($phrase);

    // Step 1: Assume there are no acceptable homophones
    $homophone = false; 

    // Step 2: Get the last letter of the word being checked
    $last_letter = mb_substr($word, -1);

    // Step 3: See if last letter of string is é and create variants if so 
    if ($last_letter == "é") {
        // Creating common variations for words ending in é
        $variant1 = $word . 'e'; 
        $variant2 = $word . 's'; 
        $variant3 = $word . 'es'; 

        $word2b = mb_substr($word, 0, -1);
        $variant4 = $word2b . 'er'; 

        // Check each variant 
        if (strpos($phrase, $variant1) !== false) $homophone = true; 
        if (strpos($phrase, $variant2) !== false) $homophone = true; 
        if (strpos($phrase, $variant3) !== false) $homophone = true; 
        if (strpos($phrase, $variant4) !== false) $homophone = true; 
    }

    // Steps for checking other variants based on different suffixes
    // Repeating the above pattern for other suffixes like 'és', 'ées', 'ée', 'ez', 'er'

    //step 4: get the last two and last three letters of the word being checked
	$last_two_letters = mb_substr($word, -2);
	$last_three_letters = mb_substr($word, -3);
		
	//step 5: see if last two letters of string are és, and create variants if so  
	if ($last_two_letters == "és")
	{
		//step 5a: get rid of last two letters
		//$word2 = str_replace('és', '', $word);
		$word2 = mb_substr($word, 0, -2);
 		
		//step 5b: create common variations for words ending in é
		$variant1 = $word2 . 'é'; 
		$variant2 = $word2 . 'ées'; 
		$variant3 = $word2 . 'ée'; 
	
		//step 5c: check each variant 
	
		if (strpos($phrase, $variant1) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant2) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant3) !== false)
			$homophone = true; 
	}
	
	//step 6: see if last two letters are -ées

	if ($last_three_letters == "ées")
	{
		//step 6a: get rid of last three letters
		$word2 = mb_substr($word, 0, -3);
		
		//step 6b: create common variations for words ending in er
		$variant1 = $word2 . 'é'; 
		$variant2 = $word2 . 'er'; 
		$variant3 = $word2 . 'ée';
		$variant4 = $word2 . 'ez';
	
		//step 6c: check each variant 
	
		if (strpos($phrase, $variant1) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant2) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant3) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant4) !== false)
			$homophone = true; 
				
	}
	
	//step 7: see if last two letters are -ée

	if ($last_two_letters == "ée")
	{
		//step 7a: get rid of last two letters
		$word2 = mb_substr($word, 0, -2);
		
		//step 7b: create common variations for words ending in er
		$variant1 = $word2 . 'é'; 
		$variant2 = $word2 . 'ées'; 
		$variant3 = $word2 . 'és';
		$variant4 = $word2 . 'er';
	
		//step 7c: check each variant 
	
		if (strpos($phrase, $variant1) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant2) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant3) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant4) !== false)
			$homophone = true; 
				
	}
	
		//step 8: see if last two letters are -EZ

	if ($last_two_letters == "ez")
	{
		//step 8a: get rid of last two letters
		$word2 = mb_substr($word, 0, -2);
		
		//step 8b: create common variations for words ending in er
		$variant1 = $word2 . 'é'; 
		$variant2 = $word2 . 'ées'; 
		$variant3 = $word2 . 'és';
		$variant4 = $word2 . 'er';
	
		//step 8c: check each variant 
	
		if (strpos($phrase, $variant1) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant2) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant3) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant4) !== false)
			$homophone = true; 
				
	}
	
	
	//step 9: see if last two letters are -ER

	if ($last_two_letters == "er")
	{
		//step 9a: get rid of last two letters
		$word2 = mb_substr($word, 0, -2);
		
		//step 9b: create common variations for words ending in er
		$variant1 = $word2 . 'é'; 
		$variant2 = $word2 . 'ées'; 
		$variant3 = $word2 . 'és';
		$variant4 = $word2 . 'ez';
	
		//step 9c: check each variant 
	
		if (strpos($phrase, $variant1) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant2) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant3) !== false)
			$homophone = true; 
		if (strpos($phrase, $variant4) !== false)
			$homophone = true; 
				
	}

    /*
    echo "<br>Transcribed word: $word<br><br>Target phrase: $phrase<br><br>Last letter: $last_letter<br><br>Last two letters: $last_two_letters<br><br>
    Variants: $variant1, $variant2, $variant3, $variant4<br><br>";  
    if ($homophone == false) {
        echo "No variants of word found in full phrase<br><br>"; }
    else {
        echo "Looks like one of your missed words is a homophone! "; }
    */

    // Return the result indicating whether a homophone was found
    return $homophone; 
}
?>