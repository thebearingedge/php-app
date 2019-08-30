<?php

$request = [
  'method' => $_SERVER['REQUEST_METHOD'],
  'path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
  'headers' => getallheaders(),
  'query' => $_GET,
  'body' => json_decode(file_get_contents('php://input')) ?? []
];

$response = [
  'status' => 200,
  'headers' => [
    'Content-Type' => 'application/json; charset=utf-8'
  ]
];

function send(&$response) {
  http_response_code($response['status']);
  foreach ($response['headers'] as $key => $value) {
    header("$key: $value");
  }
  $body = $response['body'] ?? new stdClass;
  print(json_encode($body));
  exit;
}

set_exception_handler(function ($error) {
  if ($error instanceof ApiError) {
    $status = $error->getCode();
    $message = $error->getMessage();
    $previous = $error->getPrevious();
    if ($previous) error_log($previous);
  } else {
    error_log($error);
    $status = 500;
    $message = 'An unexpected error occurred.';
  }
  $response = [
    'status' => $status,
    'headers' => [
      'Content-Type' => 'application/json; charset=utf-8'
    ],
    'body' => [
      'error' => $message
    ]
  ];
  send($response);
});


class ApiError extends Error {}

function not_found($message) {
  return new ApiError($message, 404);
}

function internal_server_error($message, $previous = null) {
  return new ApiError($message, 500, $previous);
}

function service_unavailable($message) {
  return new ApiError($message, 503);
}
