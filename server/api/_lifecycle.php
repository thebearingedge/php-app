<?php

$request = [
  'method' => $_SERVER['REQUEST_METHOD'],
  'path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
  'headers' => getallheaders(),
  'query' => $_GET,
  'body' => json_decode(file_get_contents('php://input'), true) ?? []
];

$response = [
  'status' => 200,
  'headers' => [
    'Content-Type' => 'application/json; charset=utf-8'
  ]
];

function send(&$response) {
  http_response_code($response['status']);
  if (empty($response['body'])) {
    unset($response['body']);
    unset($response['headers']['Content-Type']);
  }
  foreach ($response['headers'] as $key => $value) {
    header("$key: $value");
  }
  if (array_key_exists('body', $response)) {
    print(json_encode($response['body']));
  }
  exit;
}

function get_db_link() {
  $db_url = getenv('PHP_ENV') === 'development'
    ? getenv('DATABASE_URL')
    : getenv('CLEARDB_DATABASE_URL');
  $db_params = parse_url($db_url);
  $host = $db_params['host'];
  $user = $db_params['user'];
  $pass = $db_params['pass'];
  $database = ltrim($db_params['path'], '/');
  $link = mysqli_connect($host, $user, $pass, $database);
  if (!$link) {
    throw new ApiError('The API is temporarily down.', 503);
  }
  $link->set_charset('utf8');
  $link->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  return $link;
}

class ApiError extends Error {}

set_exception_handler(function ($error) {
  if ($error instanceof ApiError) {
    $status = $error->getCode();
    $message = $error->getMessage();
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
