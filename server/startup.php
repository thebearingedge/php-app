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

function get_database_connection() {
  $db_url = getenv('DATABASE_URL');
  $db_params = parse_url($db_url);
  $host = $db_params['host'];
  $user = $db_params['user'];
  $pass = $db_params['pass'];
  $port = $db_params['port'];
  $database = ltrim($db_params['path'], '/');
  $conn = mysqli_connect($host, $user, $pass, $database, $port);
  if (!$conn) {
    throw new Error('The API is down temporarily.', 503);
  }
  mysqli_set_charset($conn, 'utf8');
  mysqli_options($conn, MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
  return $conn;
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
