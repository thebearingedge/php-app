<?php

require_once __DIR__ . '/../api/_lifecycle.php';
require_once __DIR__ . '/../api/_errors.php';

if (getenv('PHP_ENV') === 'development') {
  $file_path = $request['path'] === '/'
    ? __DIR__ . '/index.html'
    : __DIR__ . "/${request['path']}";
  if (is_file($file_path)) {
    $file = fopen($file_path, 'r');
    fpassthru($file);
    fclose($file);
    return true;
  }
}

if (!preg_match('/^\/api\//', $request['path'])) {
  throw not_found("Cannot ${request['method']} ${request['path']}");
}

$resource = preg_replace('/^\/api\/_?/', '', $request['path']);
$handler_path = __DIR__ . "/../api/${resource}.php";

if (!is_file($handler_path)) {
  throw not_found("Cannot ${request['method']} ${request['path']}");
}

require_once $handler_path;

throw not_found("Cannot ${request['method']} ${request['path']}");
