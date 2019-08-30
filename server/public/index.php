<?php

require_once __DIR__ . '/../api/_lifecycle.php';

switch ($request['path']) {
  case '/':
    $index = fopen(__DIR__ . '/index.html', 'r');
    fpassthru($index);
    fclose($index);
    return true;
  case '/api/echo':
  case '/api/todos':
    require_once __DIR__ . "/..${request['path']}.php";
  default:
    throw not_found("Cannot ${request['method']} ${request['path']}");
}
