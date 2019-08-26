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

function send($response) {
  http_response_code($response['status']);
  foreach ($response['headers'] as $key => $value) {
    header("$key: $value");
  }
  $body = $response['body'] ?? new stdClass;
  print(json_encode($body));
  exit;
}

set_exception_handler(function ($error) {
  if ($error instanceof Exception) {
    $status = 500;
    $message = 'An unexpected error occurred.';
    error_log($error->getMessage());
  } else {
    $status = $error->getCode();
    $message = $error->getMessage();
  }
  $response = [
    'status' => $status ?? 500,
    'headers' => [
      'Content-Type' => 'application/json; charset=utf-8'
    ],
    'body' => [
      'error' => $message
    ]
  ];
  send($response);
});
