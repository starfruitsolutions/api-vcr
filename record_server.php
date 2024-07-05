<?php
// record_server.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$tapesDir = __DIR__ . '/tapes';
$tapeName = getenv('TAPE_NAME');
$apiUrl = getenv('API_URL');

$logFile = __DIR__ . '/vcr_log.txt';
function logMessage($message) {
	global $logFile;
	file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

logMessage("Server started. Tape: $tapeName, API URL: $apiUrl");

if (!$tapeName || !$apiUrl) {
	logMessage("Error: TAPE_NAME and API_URL not set");
	http_response_code(500);
	echo "TAPE_NAME and API_URL must be set as environment variables.";
	exit(1);
}

$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$url = $_SERVER['REQUEST_URI'];
$data = file_get_contents('php://input');

logMessage("Received request: $method $url");

try {
	$response = forwardRequest($apiUrl . $url, $method, $headers, $data);
	logMessage("Forwarded request to API. Response code: " . $response['status']);

	saveTape($method, $url, $headers, $data, $response);
	logMessage("Saved interaction to tape");

	sendResponse($response);
	logMessage("Sent response back to client");
} catch (Exception $e) {
	logMessage("Error: " . $e->getMessage());
	$errorResponse = [
		'status' => 'HTTP/1.1 500 Internal Server Error',
		'headers' => ['Content-Type: text/plain'],
		'body' => "An error occurred: " . $e->getMessage()
	];
	sendResponse($errorResponse);
}

function forwardRequest($url, $method, $headers, $data) {
	logMessage("Forwarding request to: $url");
	$options = [
		'http' => [
			'method' => $method,
			'header' => formatHeaders($headers),
			'content' => $data,
			'ignore_errors' => true,
			'timeout' => 30,  // 30 seconds timeout
		]
	];

	$context = stream_context_create($options);
	$result = @file_get_contents($url, false, $context);

	if ($result === false) {
		$error = error_get_last();
		throw new Exception("Failed to forward request to API: " . ($error['message'] ?? 'Unknown error'));
	}

	return [
		'body' => $result,
		'headers' => $http_response_header,
		'status' => $http_response_header[0]
	];
}

function formatHeaders($headers) {
	$formattedHeaders = [];
	foreach ($headers as $key => $value) {
		if (strtolower($key) !== 'host') {  // Skip the 'Host' header
			$formattedHeaders[] = "$key: $value";
		}
	}
	return implode("\r\n", $formattedHeaders);
}

function saveTape($method, $url, $headers, $data, $response) {
	global $tapesDir, $tapeName;

	$interaction = [
		'timestamp' => date('Y-m-d H:i:s'),
		'request' => [
			'method' => $method,
			'url' => $url,
			'headers' => $headers,
			'data' => $data
		],
		'response' => $response
	];

	$tapePath = $tapesDir . '/' . $tapeName . '.json';
	$tape = file_exists($tapePath) ? json_decode(file_get_contents($tapePath), true) : [];
	$tape[] = $interaction;
	file_put_contents($tapePath, json_encode($tape, JSON_PRETTY_PRINT));
}

function sendResponse($response) {
	logMessage("Sending response: " . $response['status']);
	// Send status code
	header($response['status']);

	// Send headers
	foreach ($response['headers'] as $header) {
		if (!strncmp($header, 'HTTP/', 5)) {
			// Skip the status line
			continue;
		}
		header($header, false);
	}

	// Send body
	echo $response['body'];
	logMessage("Response sent. Body length: " . strlen($response['body']));
}

logMessage("Request handling completed");
