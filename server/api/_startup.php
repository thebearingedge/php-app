<?php

$request = [
  'method' => $_SERVER['REQUEST_METHOD'],
  'path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
  'headers' => getallheaders(),
  'query' => $_GET,
  'body' => json_decode(file_get_contents('php://input'))
];

if (empty($request['body'])) {
  $request['body'] = [];
}

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
  if (empty($response['body'])) {
    print(json_encode(new stdClass));
  } else {
    print(json_encode($response['body']));
  }
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
    'status' => $status ? $status : 500,
    'headers' => [
      'Content-Type' => 'application/json; charset=utf-8'
    ],
    'body' => [
      'error' => $message
    ]
  ];
  send($response);
});
