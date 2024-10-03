<?php

/*

This page shows the IPA transcriptions for missed words. 

In addition, it calcualtes the most commonly missed graphemes, and gives examples in the target language

*/

session_start();
//Starts or continues session from previous PHP page

//Get config file variables and functions
include_once("../../config_ispraak.php");

//Connect to the database
$msi_connect = mysqli_connect($mysqlserv,$username,$password,$database);

//new function for getting samples of IPA

function exemplarsIPA($char,$language)
{

	//sourced from wikipedia.org/wiki/Help:IPA/French

	$exemplars = "NA"; 

	if ($language == "fr")
	{
		//consonants

		if ($char == "b") { $exemplars = "<u>b</u>on"; }
		if ($char == "d") { $exemplars = "<u>d</u>eux, gran<u>d</u>e"; }
		if ($char == "f") { $exemplars = "<u>f</u>aire, vi<u>f</u>"; }
		if ($char == "g") { $exemplars = "<u>g</u>arçon, lon<u>gu</u>e"; }
		if ($char == "k") { $exemplars = "<u>c</u>orps, ave<u>c</u>, <u>qu</u>and"; }
		if ($char == "l") { $exemplars = "<u>l</u>aisser, possib<u>l</u>e, seu<u>l</u>"; }
		if ($char == "m") { $exemplars = "<u>m</u>ême"; }
		if ($char == "n") { $exemplars = "<u>n</u>ous, bo<u>nn</u>e"; }
		if ($char == "ɲ") { $exemplars = "ga<u>gn</u>er, champa<u>gn</u>e"; }
		if ($char == "ŋ") { $exemplars = "campi<u>ng</u>, fu<u>n</u>k "; }
		if ($char == "p") { $exemplars = "<u>p</u>ère, grou<u>p</u>e"; }
		if ($char == "ʁ") { $exemplars = "<u>r</u>egarder, nôt<u>r</u>e"; }
		if ($char == "s") { $exemplars = "<u>s</u>ans, <u>ç</u>a, a<u>ss</u>ez, soi<u>x</u>ante, di<u>x</u>"; }
		if ($char == "ʃ") { $exemplars = "<u>ch</u>ance, t<u>ch</u>èque"; }
		if ($char == "t") { $exemplars = "<u>t</u>out, <u>th</u>é, <u>t</u>chèque"; }
		if ($char == "v") { $exemplars = "<u>v</u>ous, <u>w</u>agon, neu<u>f</u> heures"; }
		if ($char == "z") { $exemplars = "<u>z</u>éro, rai<u>s</u>on, cho<u>s</u>e, deu<u>x</u>ième"; }
		if ($char == "ʒ") { $exemplars = "<u>j</u>amais, vi<u>s</u>age"; }
		
		//vowels
		
		if ($char == "a") { $exemplars = "p<u>a</u>tte, l<u>à</u>, f<u>e</u>mme"; }
		if ($char == "ɑ") { $exemplars = "p<u>â</u>te, gl<u>a</u>s"; }
		if ($char == "e") { $exemplars = "cl<u>é</u>, <u>et</u>, l<u>es</u>, ch<u>ez</u>, all<u>er</u>, pi<u>ed</u>, journ<u>é</u>e"; }
		if ($char == "ɛ") { $exemplars = "b<u>aie</u>, f<u>ai</u>te, m<u>e</u>ttre, r<u>e</u>nne, cr<u>è</u>me, p<u>ei</u>ne, viol<u>et</u>"; }
		if ($char == "ɛː") { $exemplars = "f<u>ê</u>te, m<u>aît</u>re, r<u>ei</u>ne, r<u>eî</u>tre, c<u>ai</u>sse, pr<u>e</u>sse"; }
		if ($char == "ə") { $exemplars = "r<u>e</u>poser, m<u>on</u>sieur, f<u>ai</u>sons"; }
		if ($char == "i") { $exemplars = "s<u>i</u>, <u>î</u>le, rég<u>ie</u>, pa<u>y</u>s, f<u>i</u>ls"; }
		if ($char == "œ") { $exemplars = "s<u>œu</u>r, j<u>eu</u>ne, cl<u>u</u>b"; }
		if ($char == "ø") { $exemplars = "c<u>eu</u>x, j<u>eû</u>ner, qu<u>eue</u>"; }
		if ($char == "o") { $exemplars = "s<u>au</u>t, h<u>au</u>t, bur<u>eau</u>, ch<u>o</u>se, t<u>ô</u>t, c<u>ô</u>ne"; }
		if ($char == "ɔ") { $exemplars = "s<u>o</u>rt, minim<u>u</u>m, p<u>o</u>mme"; }
		if ($char == "u") { $exemplars = "c<u>ou</u>p, r<u>oue</u>"; }
		if ($char == "y") { $exemplars = "t<u>u</u>, s<u>û</u>r, r<u>ue</u>"; }
		
		//semivowels
		
		if ($char == "j") { $exemplars = "f<u>i</u>ef, pa<u>y</u>er, fi<u>ll</u>e, trava<u>il</u>, <u>hi</u>er"; }
		if ($char == "w") { $exemplars = "<u>ou</u>i, l<u>o</u>i, m<u>o</u>yen, <u>w</u>eb, <u>w</u>hisky"; }
		if ($char == "ɥ") { $exemplars = "h<u>u</u>it, P<u>u</u>y"; }
		
		//nasal vowels 
		
		if ($char == "ɑ̃") { $exemplars = "s<u>an</u>s, ch<u>am</u>p, v<u>en</u>t, t<u>em</u>ps, J<u>ean</u>"; }
		if ($char == "ɛ̃") { $exemplars = "v<u>in</u>, p<u>ain</u>, pl<u>ein</u>, bi<u>en</u>"; }
		if ($char == "œ̃") { $exemplars = "<u>un</u>, parf<u>um</u>"; }
		if ($char == "ɔ̃") { $exemplars = "s<u>on</u>, n<u>om</u>"; }
		
	}
	
	//sourced from wikipedia.org/wiki/Help:IPA/Spanish
	
	if ($language == "es")
	{
		//consonants done

		if ($char == "b") { $exemplars = "<u>b</u>estia, em<u>b</u>uste, <u>v</u>aca, en<u>v</u>idia"; }
		if ($char == "β") { $exemplars = "be<u>b</u>é, vi<u>v</u>a, cur<u>v</u>a, o<u>b</u>tuso, fút<u>b</u>ol, a<u>p</u>to"; }
		if ($char == "d") { $exemplars = "<u>de</u>do, cuan<u>do</u>, al<u>d</u>aba"; }
		if ($char == "ð") { $exemplars = "dá<u>d</u>iva, ar<u>d</u>er, a<u>d</u>mirar, juventu<u>d</u>, a<u>t</u>mósfera"; }
		if ($char == "f") { $exemplars = "<u>f</u>ase, a<u>f</u>gano"; }
		if ($char == "g") { $exemplars = "<u>g</u>ato, <u>gu</u>erra, len<u>g</u>ua"; }
		if ($char == "ɣ") { $exemplars = "tri<u>g</u>o, amar<u>g</u>o, si<u>g</u>no, do<u>c</u>tor"; }
		if ($char == "ʝ") { $exemplars = "a<u>y</u>uno"; }
		if ($char == "ɟ") { $exemplars = "<u>y</u>ermo, cón<u>y</u>uge"; }
		if ($char == "k") { $exemplars = "<u>c</u>aña, <u>qu</u>ise, <u>k</u>ilo"; }
		if ($char == "l") { $exemplars = "<u>l</u>ino</u>"; }
		if ($char == "m") { $exemplars = "<u>m</u>adre, ca<u>m</u>po"; }
		if ($char == "ɱ") { $exemplars = "a<u>n</u>fibio"; }
		if ($char == "n") { $exemplars = "<u>n</u>ido, si<u>n</u>, álbu<u>m</u>"; }
		if ($char == "ɲ") { $exemplars = "<u>ñ</u>andú, có<u>n</u>yuge"; }
		if ($char == "ŋ") { $exemplars = "ci<u>n</u>co, te<u>n</u>go"; }
		if ($char == "p") { $exemplars = "<u>p</u>ozo"; }
		if ($char == "r") { $exemplars = "<u>r</u>umbo, ca<u>rr</u>o, hon<u>ra</u> (trilled)"; }
		if ($char == "ɾ") { $exemplars = "ca<u>r</u>o, b<u>r</u>avo, pa<u>r</u>ti<u>r</u>"; }
		if ($char == "s") { $exemplars = "<u>s</u>aco, e<u>s</u>pita, <u>x</u>enón"; }
		if ($char == "θ") { $exemplars = "<u>c</u>ereal, <u>z</u>orro, ja<u>z</u>mín, ju<u>z</u>gar"; }
		if ($char == "ʃ") { $exemplars = "<u>sh</u>ow, Ro<u>ch</u>er, Frei<u>x</u>enet"; }
		if ($char == "t") { $exemplars = "<u>t</u>amiz"; }
		if ($char == "x") { $exemplars = "<u>j</u>amón, <u>g</u>eneral, Mé<u>x</u>ico, <u>h</u>ámster"; }
		if ($char == "ʎ") { $exemplars = "<u>ll</u>ave, po<u>ll</u>o"; }
 
		//vowels done 
		
		if ($char == "a") { $exemplars = "m<u>a</u>l"; }
		if ($char == "e") { $exemplars = "<u>e</u>s"; }
		if ($char == "i") { $exemplars = "d<u>i</u>, <u>y</u>"; }
		if ($char == "o") { $exemplars = "s<u>o</u>l"; }
		if ($char == "u") { $exemplars = "s<u>u</u>"; }
		
		//semivowels done
		
		if ($char == "j") { $exemplars = "c<u>i</u>uadad, re<u>y</u>"; }
		if ($char == "w") { $exemplars = "c<u>u</u>atro, H<u>u</u>ila, a<u>u</u>to, ping<u>ü</u>ino"; }

		//stress and syllabification done 
		
		if ($char == "ˈ") { $exemplars = "ciu<u>dad</u>"; }
		if ($char == ".") { $exemplars = "<u>mí</u>o"; }

		
	}       

	return $exemplars; 
}


//Assume there is no error fetching grades
$error = ""; 

if (mysqli_connect_errno())
{
  	echo "Unable to connect to the database. Please try again later.";
  	$error = "Unable to connect to database."; 
  	//echo "Failed to connect to MySQL because: " . mysqli_connect_error();
}


//Get mykey from query string & declare session variable
//If no variable found in query string, just initialize as Not Available
$mykey = $_GET['mykey'] ?? 'NA';
$mykey2 = $_GET['mykey2'] ?? 'NA';
$email = $_GET['instructor_email'] ?? 'NA';

$readable_date=date('m/d/y', $mykey);



//Get variables from iSpraak GRADES table
$result = mysqli_query($msi_connect,"SELECT * FROM ispraak_stats WHERE activity_id='$mykey' AND misc='$mykey2' AND instructor_email='$email' ORDER BY missed_word ASC");
$num=mysqli_num_rows($result);


$temail_hide = hide_email($email);
 


if ($num < 1)
{
  	$error = "<br>Sorry, there are no statistics available for this activity.<br><br>"; 
    echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title><link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=3\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script></head>
<body id=\"main_body\" ><img id=\"top\" src=\"images/top.png\" alt=\"\">
<div id=\"form_container\"><div id=\"headerBar\"></div>
<form id=\"ispraak\" class=\"ispraak_form\"  method=\"post\" action=\"#\">
<div class=\"form_description\">
<img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\" align=\"left\"> 
<br><br><br>$error";

}
else{


echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>  
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
<title>iSpraak</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"css/ispraak.css?v=3\" media=\"all\">
<script type=\"text/javascript\" src=\"javascript/ispraak.js\"></script>
</head>
 
<body id=\"main_body\" >
  
   <img id=\"top\" src=\"images/top.png\">
   <div id=\"form_container\">
   <div id=\"headerBar\"></div>
  
       <form id=\"form_1007732\" class=\"ispraak_form\" enctype=\"multipart/form-data\" method=\"post\" action=\"makeit.php\">
                   <div class=\"form_description\">
                  
                   <img style=\"float: left; padding: 0px 20px 0px 0px\" src=\"images/logo5.png\" height=\"35\" alt=\"iSpraak-Logo\">

			<br><br><br>IPA Insights since $readable_date for $temail_hide</b> 
			
			<br></p>
						
		</div>						
			<ul >";

//get some basic display info for this activity

$myresultstats = mysqli_query($msi_connect, "SELECT * FROM ispraak where mykey='$mykey' AND mykey2='$mykey2'");
$row = mysqli_fetch_array($myresultstats);
$language=$row["language"];
$blocktext=$row["blocktext"];

//let's change the name of the table look up, depending on the langauge
$ipa_table = "ipa_french";
if ($language == "es")
{
$ipa_table = "ipa_spanish";
}

			

//want this stuff to float left 

echo "<div style=\"float:left;width:300px\">";
echo "<a href=\"stats.php?mykey=$mykey&mykey2=$mykey2&instructor_email=$email\" class=\"cutelink3\">Missed Words</a> / <a href=\"stats_progress.php?mykey=$mykey&mykey2=$mykey2&instructor_email=$email\" class=\"cutelink3\">Progress</a> / <SPAN STYLE=\"background-color: #E6E6E6\">IPA</SPAN><br><br>"; 


$first_word = mysqli_result($result,0,"missed_word");
$stand_count = 0; 
$darray = array();


//escape words
$first_word_escaped = mysqli_real_escape_string($msi_connect, $first_word);




$resultC = mysqli_query($msi_connect,"SELECT * FROM ispraak_stats WHERE activity_id='$mykey' AND missed_word='$first_word_escaped'");
$numC=mysqli_num_rows($resultC);

//nicer display if possible
$equal_columns = ($num / 2);
$equal_columns = round($equal_columns);
echo "<div class=\"row\"><div class=\"column\">";

//get IPA for first word
$result_ipa_01 = mysqli_query($msi_connect,"SELECT * FROM $ipa_table WHERE word='$first_word_escaped'");
$ipa_01 = mysqli_result($result_ipa_01,0,"ipa");
//build IPA string
$ipa_for_all_words = $ipa_01; 


for ($i = 1; $i < $numC; $i++)
{
	$ipa_for_all_words = $ipa_for_all_words . $ipa_01; 
}


//update string with formatting
$ipa_01 = "<br><span style=\"color:gray\">$ipa_01</span>"; 


echo "$first_word ($numC) $ipa_01<br><br>"; 

//echo "$lecture"; added by VJ



for ($i = 0; $i < $num; $i++)
{
  
  $mw=mysqli_result($result,$i,"missed_word");
  
  //escape words
	$mw_escaped = mysqli_real_escape_string($msi_connect, $mw);

  
  //build up items in your array for the stats graph

  $darray[] = $mw;  
  
  if ($mw !== "$first_word")
  {
  	echo "$mw "; 
  	
  	
  	$resultB = mysqli_query($msi_connect,"SELECT * FROM ispraak_stats WHERE activity_id='$mykey' AND missed_word='$mw_escaped'");
	$numB=mysqli_num_rows($resultB);
	
	//get IPA for next word
	$result_ipa_01 = mysqli_query($msi_connect,"SELECT * FROM $ipa_table WHERE word='$mw_escaped'");
	$ipa_01 = mysqli_result($result_ipa_01,0,"ipa");
	
	for ($z = 0; $z < $numB; $z++) 
	{
		//keep building long IPA string
		$ipa_for_all_words = $ipa_for_all_words . $ipa_01; 
	}
	
	
	$ipa_01 = "<br><span style=\"color:gray\">$ipa_01</span>"; 

  	echo "($numB) $ipa_01<br><br>"; 
  	
  	$stand_count = 1;
  	$first_word = $mw;
  }
  else
  {
    $stand_count++; 
  }
  
  if ($i == $equal_columns)
  {
  	echo "</div><div class=\"column\">";
  
  }
  
}

// end two column display
echo "</div></div>";

//end left float

echo "</div>";



echo "<div style=\"float:right;width:300px\">";

//Check out product of lengthy IPA string

$ipa_for_all_words = str_replace('/', '', $ipa_for_all_words);

$chars = preg_split('/(?=\p{L})/u', $ipa_for_all_words, null, PREG_SPLIT_NO_EMPTY);


$counts = array_count_values($chars);
arsort($counts);
$list = array_keys($counts);

$ipa_most_missed = array_keys($counts);
$ipa_most_missed_key = array_shift($ipa_most_missed);
unset($counts["$ipa_most_missed_key"]) ;
$ipa_most_missed_2 = array_keys($counts);
$ipa_most_missed_key_2 = array_shift($ipa_most_missed_2);
unset($counts["$ipa_most_missed_key_2"]) ;
$ipa_most_missed_3 = array_keys($counts);
$ipa_most_missed_key_3 = array_shift($ipa_most_missed_3);
unset($counts["$ipa_most_missed_key_3"]) ;
$ipa_most_missed_4 = array_keys($counts);
$ipa_most_missed_key_4 = array_shift($ipa_most_missed_4);
unset($counts["$ipa_most_missed_key_4"]) ;
$ipa_most_missed_5 = array_keys($counts);
$ipa_most_missed_key_5 = array_shift($ipa_most_missed_5);

//IPA exemplars 

//$language = "fr"; 
$ipa_exemplar1 = exemplarsIPA($ipa_most_missed_key,$language);
$ipa_exemplar2 = exemplarsIPA($ipa_most_missed_key_2,$language);
$ipa_exemplar3 = exemplarsIPA($ipa_most_missed_key_3,$language);
$ipa_exemplar4 = exemplarsIPA($ipa_most_missed_key_4,$language);
$ipa_exemplar5 = exemplarsIPA($ipa_most_missed_key_5,$language);

//Get Readable Language
$readable_language = outputLanguage($language); 


if ($language == "fr" || $language == "es")
{
	echo "<br><br><span style=\"color:#364563\">IPA Frequency Insights for this Activity: <br><br>$ipa_most_missed_key ($ipa_exemplar1)<br><br>$ipa_most_missed_key_2 ($ipa_exemplar2)<br><br>$ipa_most_missed_key_3 ($ipa_exemplar3)<br><br>$ipa_most_missed_key_4 ($ipa_exemplar4)<br><br> $ipa_most_missed_key_5 ($ipa_exemplar5)</span>"; 
}
else
{

	echo "<br><br><p style=\"color:red\">IPA Insights is an experimental feature, and $readable_language is not currently supported.<br><br> Let the iSpraak Labs team know if you would like to see this feature further develeoped for $readable_language. <br><br>Contact us at: help@ispraak.net</p>"; 
}

echo "<br><br><br><br><span style=\"color:gray\">Original text ($language): $blocktext</span>"; 



//end floating right div
echo "<br><br></div>";			
			
			
echo "

			</ul>
		</form>	
		<div id=\"footer\">
			© D. Nickolai
		</div>
	</div>
	</body>
</html>";

//close your connection to the DB
mysqli_close($msi_connect);
}


?>