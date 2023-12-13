<?php

require_once 'vendor/autoload.php';
include_once("./functions.php");

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Confirm configuration file is in correct location
$directory_test = $_ENV['DIRECTORY_TEST'];

// Admin view of recent activity
$ispraak_admin_email = $_ENV['ISPRAAK_ADMIN_EMAIL'];

// Database variables
$mysqlserv = $_ENV['MYSQLSERV'];
$username = $_ENV['USERNAME'];
$database = $_ENV['DATABASE'];
$password = $_ENV['PASSWORD'];

// Domain name for this installation
$domain_name = $_ENV['DOMAIN_NAME'];

// Server specific path for moving uploaded MP3 files
$ispraak_full_mp3_server_path = $_ENV['ISPRAAK_FULL_MP3_SERVER_PATH'];

// Installation specific salt hash
$ispraak_salt = $_ENV['ISPRAAK_SALT'];

// Cron job variable key
$ispraak_cronkey = $_ENV['ISPRAAK_CRONKEY'];

// Google Specific Keys
$google_clientID = $_ENV['GOOGLE_CLIENT_ID'];
$google_clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
$google_redirectUri = $_ENV['GOOGLE_REDIRECT_URI'];

// Mail variables
$mail_host = $_ENV['MAIL_HOST'];
$mail_port = $_ENV['MAIL_PORT'];
$mail_username = $_ENV['MAIL_USERNAME'];
$mail_password = $_ENV['MAIL_PASSWORD'];
$mail_content = $_ENV['MAIL_CONTENT'];
$mail_mime = $_ENV['MAIL_MIME'];
$mail_reply_address = $_ENV['MAIL_REPLY_ADDRESS'];

//Secondary mail variables in case one server not punching through blacklist
$mail_host2 = $_ENV['MAIL_HOST2'];
$mail_port2 = $_ENV['MAIL_PORT2'];
$mail_username2 = $_ENV['MAIL_USERNAME2'];
$mail_password2 = $_ENV['MAIL_PASSWORD2'];
$mail_content2 = $_ENV['MAIL_CONTENT2'];
$mail_mime2 = $_ENV['MAIL_MIME2'];
$mail_reply_address2 = $_ENV['MAIL_REPLY_ADDRESS2'];

//How do you want to handle mail?
//Enter a value of 1 for server default
//Enter a value of 2 for Mail.php  (Pear Mail Library)
//Enter a value of 3 for no emails

// Mail handling option
$mail_default_engine = $_ENV['MAIL_DEFAULT_ENGINE'];

//Developer Keys for 3rd party APIs
//$ispeech_key = "developerdemokeydeveloperdemokey";
$globse_key = $_ENV['GLOBSE_KEY'];
$google_key = $_ENV['GOOGLE_KEY'];
$ispeech_key = $_ENV['ISPEECH_KEY'];
$twitter_key = $_ENV['TWITTER_KEY'];
$azure_api_tranliteration_key = $_ENV['AZURE_API_TRANSLITERATION_KEY']; //expires 12/2/22
$azure_client_id = $_ENV['AZURE_CLIENT_ID'];
$azure_client_secret = $_ENV['AZURE_CLIENT_SECRET'];

//note to self - azure account with help@ispraak.com address

// JPGraph for Graphing Stats
//Do you want to use JPGraph for Graphing Top Missed Words and other Stats
//1=Yes, 2=No
$jp_graph_activated = $_ENV['JP_GRAPH_ACTIVATED'];

// Frequently re-used strings of text for app
$ispraak_header = $_ENV['ISPRAAK_HEADER'];
$ispraak_logo = $_ENV['ISPRAAK_LOGO'];
$ispraak_footer = $_ENV['ISPRAAK_FOOTER'];
$ispraak_menu = $_ENV['ISPRAAK_MENU'];

?>