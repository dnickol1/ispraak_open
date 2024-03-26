<?php
require_once __DIR__ . '../../../vendor/autoload.php';
require_once __DIR__ . '/db/database.php';

use Packback\Lti1p3; 

LTI\JWKS_Endpoint::new([
    'fcec4f14-28a5-4697-87c3-e9ac361dada5' => file_get_contents(__DIR__ . '/db/platform.key')
])->output_jwks();

?>