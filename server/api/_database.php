<?php

function get_database_connection() {
  $db_url = getenv('PHP_ENV') === 'development'
    ? getenv('DATABASE_URL')
    : getenv('CLEARDB_DATABASE_URL');
  $db_params = parse_url($db_url);
  $host = $db_params['host'];
  $user = $db_params['user'];
  $pass = $db_params['pass'];
  $database = ltrim($db_params['path'], '/');
  $port = $db_params['port'];
  $conn = mysqli_connect($host, $user, $pass, $database, $port);
  if (!$conn) {
    throw service_unavailable('The API is temporarily down.');
  }
  mysqli_set_charset($conn, 'utf8');
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  mysqli_options($conn, MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
  return $conn;
}
