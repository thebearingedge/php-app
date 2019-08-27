<?php

require_once __DIR__ . '/../api/_lifecycle.php';
require_once __DIR__ . '/../api/_errors.php';

if ($request['path'] === '/') {
  $index = fopen(__DIR__ . '/index.html', 'r');
  fpassthru($index);
  fclose($index);
  return true;
}

switch ($request['path']) {
  case '/api/echo':
  case '/api/todos':
    require_once __DIR__ . "/..${request['path']}.php";
  default:
    throw not_found("Cannot ${request['method']} ${request['path']}");
}
