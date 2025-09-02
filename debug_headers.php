<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ob_start();

header('Content-Type: application/json');

// Capture all request information
$debug_info = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'input' => file_get_contents('php://input'),
    'get' => $_GET,
    'post' => $_POST,
    'server' => [
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
        'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? ''
    ]
];

// Try to decode JSON input
$json_input = json_decode($debug_info['input'], true);
if ($json_input !== null) {
    $debug_info['parsed_json'] = $json_input;
}

ob_clean();
echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>