<?php
include_once("../../config_ispraak.php");
require_once '../../../vendor/autoload.php';
use Packback\Lti1p3; 
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

$jwksUrl = 'https://lms.example.com/.well-known/jwks.json';
$jwt = $_POST['id_token'] ?? ''; // Ensure this aligns with how data is sent

if (!$jwt) {
    die("No JWT provided.");
}

function getPublicKey($kid, $jwksUrl) {
    $jwksJson = file_get_contents($jwksUrl);
    $keys = json_decode($jwksJson, true);
    $keySet = JWK::parseKeySet($keys);
    return $keySet[$kid] ?? null;
}

function findOrCreateUser($userId) {
    // Implement user lookup/creation logic here
    // This is a placeholder function
    return $userId;
}

function authenticateUser($user) {
    // Implement authentication logic here
    // This might involve setting session variables, etc.
}

function getAssignmentUrl($courseId, $assignmentId) {
    // Implement logic to determine the assignment URL
    // This is a placeholder function
    return "/path/to/assignment?course=$courseId&assignment=$assignmentId";
}

try {
    $decodedHeader = JWT::decode($jwt, new Key('file://path_to_public_key.pem', 'RS256'), ['RS256']);
    $kid = $decodedHeader->header->kid;
    $publicKey = getPublicKey($kid, $jwksUrl);

    if (!$publicKey) {
        throw new Exception("Public key not found for kid: $kid");
    }

    $decoded = JWT::decode($jwt, $publicKey, ['RS256']);
    
    // Extract necessary claims
    $userId = $decoded->sub;
    $courseId = $decoded->{'https://purl.imsglobal.org/spec/lti/claim/context'}->id;
    $assignmentId = $decoded->{'https://purl.imsglobal.org/spec/lti/claim/resource_link'}->id;

    $user = findOrCreateUser($userId);
    authenticateUser($user);

    $assignmentUrl = getAssignmentUrl($courseId, $assignmentId);
    header('Location: ' . $assignmentUrl);
    exit;
} catch (Exception $e) {
    die("Error validating JWT: " . $e->getMessage());
}
?>