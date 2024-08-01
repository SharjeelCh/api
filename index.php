<?php
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include 'DbConnect.php';
include 'signup.php';
include 'login.php';
include 'forgetPassword.php';
include 'verify.php';
require_once 'sessionHandler.php';

$db = new DbConnect();
$conn = $db->connect();
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database Connection Failed']);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

$endpoint = $uri[2];

switch ($endpoint) {
    case 'signup':
        handleSignup($conn);
        break;
    case 'login':
        handleLogin($conn);
        break;
    case 'logout':
        logoutUser();
        echo json_encode(['status' => 'success', 'message' => 'User logged out successfully']);
        break;        
    case 'forgetpassword':
        handleForgetPassword($conn);
        break;
    case 'verify':
        handleVerify($conn);
        break;
    case 'searchUserByEmail':
        searchUserByEmail($conn);
        break;    
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Endpoint']);
        break;
}
?>
