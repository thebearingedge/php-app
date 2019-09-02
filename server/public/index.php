<?php

require_once __DIR__ . '/../api/_lifecycle.php';

switch ($request['path']) {
  case '/':
    readfile('index.html');
    exit;
  case '/api/todos':
    require_once __DIR__ . "/..${request['path']}.php";
  default:
    throw new ApiError("Cannot ${request['method']} ${request['path']}", 404);
}
